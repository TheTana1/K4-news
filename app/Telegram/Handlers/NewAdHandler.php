<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use App\Services\TelegramService;
use App\Services\UserRegistrationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NewAdHandler
{
    public function __construct(
        readonly UserRegistrationService $userRegistrationService,
        readonly TelegramService $telegramService,
    ) {
    }

    public function handle($chatId, $userDb): bool
    {
        if (!$chatId) {
            return $this->telegramService->sendMessage($chatId, '❌ Ошибка чата');
        }
        if (!$userDb) {
            return $this->telegramService->sendMessage($chatId, '❌ Ошибка базы данных');
        }

        session(["ad_user_{$chatId}" => [
            'name' => $userDb->name,
            'telegram_id' => $userDb->telegram_id ?? null,
            'telegram_username' => $userDb->telegram_username ?? null,
            'user_id' => $userDb->id,
        ]]);

        $sessionKey = "ad_{$chatId}";
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
            if ($data['step'] == 2) {
                return $this->askForFile($chatId);
            }
            if ($data['step'] == 3) {
                return $this->askForAudience($chatId);
            }
            if ($data['step'] == 4) {
                return $this->confirmAd($chatId, $data);
            }
        }

        session([$sessionKey => ['step' => 1, 'files' => []]]);

        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "📝 Напишите текст вашего объявления.\n\nДля отмены нажмите /cancel",
            [[['text' => '❌ Отмена']]]
        );
    }

    public function handleMessage($message): bool
    {
        $chatId = $message->chat->id ?? null;
        if (!$chatId) return false;

        $text = $message->text ?? '';
        $sessionKey = "ad_{$chatId}";
        $data = session($sessionKey, ['step' => 1, 'files' => []]);

        if ($text === '❌ Отмена' || $text === '/cancel') {
            session()->forget($sessionKey);
            session()->forget("ad_user_{$chatId}");

            return $this->telegramService->sendWithRemoveKeyboard(
                $chatId,
                '❌ Создание объявления отменено.'
            );
        }

        if ($data['step'] == 1) {
            if (empty($text)) {
                return $this->telegramService->sendMessage(
                    $chatId,
                    '❌ Текст объявления не может быть пустым. Попробуйте снова.'
                );
            }

            $data['text'] = $text;
            $data['step'] = 2;
            session([$sessionKey => $data]);

            return $this->askForFile($chatId);
        }

        if ($data['step'] == 2) {
            if ($text === '✅ Готово') {
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId);
            }

            if ($text === '⏭ Пропустить') {
                $data['files'] = [];
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId);
            }

            $filesInfo = $this->processAllFiles($message);

            if (!empty($filesInfo)) {
                foreach ($filesInfo as $fileInfo) {
                    $data['files'][] = $fileInfo;
                }

                session([$sessionKey => $data]);

                $count = count($data['files']);
                $msg = "✅ Загружено файлов: {$count}\n\n";
                $msg .= "Отправьте еще файлы или нажмите 'Готово' для продолжения.";

                return $this->telegramService->sendWithKeyboard(
                    $chatId,
                    $msg,
                    [
                        [['text' => '✅ Готово']],
                        [['text' => '⏭ Пропустить']],
                        [['text' => '❌ Отмена']],
                    ]
                );
            }

            return $this->telegramService->sendMessage(
                $chatId,
                '❌ Пожалуйста, отправьте фото, документ или нажмите "Готово".'
            );
        }

        if ($data['step'] == 3) {
            $audienceMap = [
                '👥 Всем' => 2,
                '🍳 Сотрудникам кухни' => 3,
                '🛎 Сотрудникам зала' => 4
            ];

            if (isset($audienceMap[$text])) {
                $data['role_id'] = $audienceMap[$text];
                $data['step'] = 4;
                session([$sessionKey => $data]);
                return $this->confirmAd($chatId, $data);
            }

            if ($text === '⬅️ Назад') {
                $data['step'] = 2;
                session([$sessionKey => $data]);
                return $this->askForFile($chatId);
            }

            return $this->askForAudience($chatId);
        }

        if ($data['step'] == 4) {
            if ($text === '✅ Опубликовать') {
                return $this->publishAd($chatId, $data);
            }

            if ($text === '✏️ Изменить текст') {
                $data['step'] = 1;
                session([$sessionKey => $data]);

                return $this->telegramService->sendWithKeyboard(
                    $chatId,
                    "✏️ Введите новый текст объявления:",
                    [[['text' => '❌ Отмена']]]
                );
            }

            if ($text === '👥 Изменить получателей') {
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId);
            }

            if ($text === '❌ Отмена') {
                session()->forget($sessionKey);
                session()->forget("ad_user_{$chatId}");

                return $this->telegramService->sendWithRemoveKeyboard(
                    $chatId,
                    '❌ Создание объявления отменено.'
                );
            }

            return $this->confirmAd($chatId, $data);
        }

        return $this->telegramService->sendMessage(
            $chatId,
            '⚠️ Непонятная команда. Используйте кнопки.'
        );
    }

    private function askForFile($chatId): bool
    {
        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "📸 Отправьте фото или файлы для объявления.\nИли нажмите 'Пропустить'",
            [
                [['text' => '⏭ Пропустить']],
                [['text' => '❌ Отмена']],
            ]
        );
    }

    private function askForAudience($chatId): bool
    {
        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "👥 Кому отправить объявление?\n\nВыберите аудиторию:",
            [
                [['text' => '👥 Всем'], ['text' => '🍳 Сотрудникам кухни']],
                [['text' => '🛎 Сотрудникам зала']],
                [['text' => '⬅️ Назад'], ['text' => '❌ Отмена']],
            ]
        );
    }

    private function confirmAd($chatId, $data): bool
    {
        $roleLabels = [
            2 => '👥 Всем',
            3 => '🍳 Сотрудникам кухни',
            4 => '🛎 Сотрудникам зала'
        ];

        $text = "✅ Проверьте объявление:\n\n";
        $text .= "📝 Текст:\n{$data['text']}\n\n";
        $text .= "👥 Получатели: " . ($roleLabels[$data['role_id'] ?? 2] ?? '👥 Всем') . "\n\n";

        if (!empty($data['files']) && is_array($data['files'])) {
            $text .= "📎 Файлы (всего: " . count($data['files']) . "):\n";
            foreach ($data['files'] as $index => $file) {
                $text .= "  " . ($index + 1) . ". {$file['file_name']}\n";
            }
        } else {
            $text .= "📎 Без файлов\n";
        }

        $text .= "\nПодтвердите публикацию или отредактируйте.";

        return $this->telegramService->sendWithKeyboard(
            $chatId,
            $text,
            [
                [['text' => '✅ Опубликовать'], ['text' => '👥 Изменить получателей']],
                [['text' => '✏️ Изменить текст']],
                [['text' => '❌ Отмена']],
            ]
        );
    }

    private function processAllFiles($message)
    {
        $files = [];

        if (isset($message->photo) && !empty($message->photo)) {
            $photoArray = $message->photo;
            $lastKey = array_key_last($photoArray);
            $photo = $photoArray[$lastKey];

            $fileInfo = $this->processPhoto($photo);
            if ($fileInfo) {
                $files[] = $fileInfo;
            }
        }

        if (isset($message->document)) {
            $fileInfo = $this->processDocument($message->document);
            if ($fileInfo) {
                $files[] = $fileInfo;
            }
        }

        return $files;
    }

    private function processPhoto($photo): array|null
    {
        try {
            $fileId = $photo->file_id;
            $fileName = 'photo_' . time() . '_' . uniqid() . '.jpg';
            $mimeType = 'image/jpeg';

            return $this->downloadAndSaveFile($fileId, $fileName, $mimeType);
        } catch (\Exception $e) {
            Log::error('Ошибка обработки фото: ' . $e->getMessage());
            return null;
        }
    }

    private function processDocument($document): array|null
    {
        try {
            $fileId = $document->file_id;
            $fileName = $document->file_name ?? 'document_' . time() . '_' . uniqid();
            $mimeType = $document->mime_type ?? 'application/octet-stream';

            return $this->downloadAndSaveFile($fileId, $fileName, $mimeType);
        } catch (\Exception $e) {
            Log::error('Ошибка обработки документа: ' . $e->getMessage());
            return null;
        }
    }

    private function downloadAndSaveFile($fileId, $fileName, $mimeType): array|null
    {
        try {
            $file = TeleBot::getFile(['file_id' => $fileId]);

            $fileContent = file_get_contents($file->url(config('telebot.bots.default.token')));

            if ($fileContent === false) {
                Log::error('Не удалось скачать файл: ' . $fileId);
                return null;
            }

            $lastAd = Advertisement::query()->latest('id')->first();
            $nextId = $lastAd ? $lastAd->id + 1 : 1;

            $path = 'advertisements/' . $nextId . '/' . $fileName;
            Storage::disk('public')->put($path, $fileContent);

            return [
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->file_size ?? 0,
                'mime_type' => $mimeType,
                'disk' => 'public',
                'file_id' => $fileId,
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка скачивания файла: ' . $e->getMessage());
            return null;
        }
    }

    private function publishAd($chatId, $data): bool
    {
        DB::beginTransaction();
        try {
            $telegramUser = session("ad_user_{$chatId}") ?? null;

            $ad = Advertisement::create([
                'content' => $data['text'],
                'user_id' => User::where('telegram_username', $telegramUser['telegram_username'])->value('id') ?? null,
                'status' => 'active',
                'published_at' => now(),
                'role_id' => $data['role_id'] ?? 2,
            ]);

            if (!empty($data['files']) && is_array($data['files'])) {
                foreach ($data['files'] as $fileData) {
                    if (empty($fileData['file_path']) || empty($fileData['file_name'])) {
                        Log::warning('Incomplete file data', ['fileData' => $fileData]);
                        continue;
                    }

                    $oldPath = $fileData['file_path'];
                    $newPath = 'advertisements/' . $ad->id . '/' . $fileData['file_name'];

                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->move($oldPath, $newPath);
                        $fileData['file_path'] = $newPath;
                    }

                    $ad->files()->create($fileData);
                }
            }

            DB::commit();

            Cache::tags(['advertisements-index'])->flush();
            Cache::tags(['dashboard'])->flush();

            session()->forget("ad_{$chatId}");
            session()->forget("ad_user_{$chatId}");

            $roleLabels = [
                2 => 'всем',
                3 => 'сотрудникам кухни',
                4 => 'сотрудникам зала'
            ];

            $text = "✅ <b>Объявление успешно опубликовано!</b>\n\n" .
                "🆔 ID: {$ad->id}\n" .
                "👥 Отправлено: " . ($roleLabels[$data['role_id'] ?? 2] ?? 'всем') . "\n" .
                "📅 Дата: " . now()->format('d.m.Y H:i');

            return $this->telegramService->sendHtmlMessage(
                $chatId,
                $text,
                [
                    'reply_markup' => [
                        'keyboard' => [
                            [['text' => '🏠 На главную']],
                        ],
                        'resize_keyboard' => true,
                    ],
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка публикации объявления: ' . $e->getMessage(), [
                'chat_id' => $chatId,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->telegramService->sendWithRemoveKeyboard(
                $chatId,
                '❌ Произошла ошибка при публикации. Попробуйте позже.'
            );
        }
    }
}

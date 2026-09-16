<?php

namespace App\Telegram\Handlers;

use App\Services\UserRegistrationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Models\Advertisement;
use App\Models\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Objects\Message;

class NewAdHandler
{
    public function __construct(readonly UserRegistrationService $userRegistrationService)
    {
    }

    public function handle($chatId,  $userDb):Message
    {
        if (!$chatId) {
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Ошибка чата',
                'reply_markup' => [
                    'resize_keyboard' => true,
                ],
            ]);
        }
        if (!$userDb) {
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Ошибка базы данных',
                'reply_markup' => [
                    'resize_keyboard' => true,
                ],
            ]);
        }

        session(["ad_user_{$chatId}" => [
            'name' => $userDb->name,
            'telegram_id' => $userDb->telegram_id ?? null,
            'telegram_username' => $userDb->telegram_username ?? null,
            'user_id' => $userDb->id,
        ]]);

        // Проверяем, есть ли незавершённое объявление
        $sessionKey = "ad_{$chatId}";
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
            if ($data['step'] == 2) {
                return $this->askForFile($chatId);
            }
            if ($data['step'] == 3) {
                return $this->askForAudience($chatId, $data);
            }
            if ($data['step'] == 4) {
                return $this->confirmAd($chatId, $data);
            }
        }

        // Начинаем новый процесс
        session([$sessionKey => ['step' => 1, 'files' => []]]);

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "📝 Напишите текст вашего объявления.\n\n" .
                "Для отмены нажмите /cancel",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '❌ Отмена']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    public function handleMessage($message)
    {
        $chatId = $message->chat->id ?? null;
        if (!$chatId) return false;

        $text = $message->text ?? '';
        $sessionKey = "ad_{$chatId}";
        $data = session($sessionKey, ['step' => 1, 'files' => []]);

        // Отмена
        if ($text === '❌ Отмена' || $text === '/cancel') {
            session()->forget($sessionKey);
            session()->forget("ad_user_{$chatId}");

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Создание объявления отменено.',
                'reply_markup' => ['remove_keyboard' => true],
            ]);
        }

        // Шаг 1: Получаем текст
        if ($data['step'] == 1) {
            if (empty($text)) {
                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❌ Текст объявления не может быть пустым. Попробуйте снова.',
                ]);
            }

            $data['text'] = $text;
            $data['step'] = 2;
            session([$sessionKey => $data]);

            return $this->askForFile($chatId);
        }

        // Шаг 2: Загрузка файлов
        if ($data['step'] == 2) {
            if ($text === '✅ Готово') {
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId, $data);
            }

            if ($text === '⏭ Пропустить') {
                $data['files'] = [];
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId, $data);
            }

            // Обрабатываем файлы
            $filesInfo = $this->processAllFiles($message);

            if (!empty($filesInfo)) {
                foreach ($filesInfo as $fileInfo) {
                    $data['files'][] = $fileInfo;
                }

                session([$sessionKey => $data]);

                $count = count($data['files']);
                $text = "✅ Загружено файлов: {$count}\n\n";
                $text .= "Отправьте еще файлы или нажмите 'Готово' для продолжения.";

                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'reply_markup' => [
                        'keyboard' => [
                            [['text' => '✅ Готово']],
                            [['text' => '⏭ Пропустить']],
                            [['text' => '❌ Отмена']],
                        ],
                        'resize_keyboard' => true,
                    ],
                ]);
            }

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Пожалуйста, отправьте фото, документ или нажмите "Готово".',
            ]);
        }

        // Шаг 3: Выбор аудитории
        if ($data['step'] == 3) {
            // Маппинг текста кнопок на role_id
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

            // Если нажали "Назад"
            if ($text === '⬅️ Назад') {
                $data['step'] = 2;
                session([$sessionKey => $data]);
                return $this->askForFile($chatId);
            }

            // Если просто текст - показываем выбор снова
            return $this->askForAudience($chatId, $data);
        }

        // Шаг 4: Подтверждение
        if ($data['step'] == 4) {
            if ($text === '✅ Опубликовать') {
                return $this->publishAd($chatId, $data);
            }

            if ($text === '✏️ Изменить текст') {
                $data['step'] = 1;
                session([$sessionKey => $data]);

                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✏️ Введите новый текст объявления:",
                    'reply_markup' => [
                        'keyboard' => [
                            [['text' => '❌ Отмена']],
                        ],
                        'resize_keyboard' => true,
                    ],
                ]);
            }

            if ($text === '👥 Изменить получателей') {
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId, $data);
            }

            if ($text === '❌ Отмена') {
                session()->forget($sessionKey);
                session()->forget("ad_user_{$chatId}");

                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❌ Создание объявления отменено.',
                    'reply_markup' => ['remove_keyboard' => true],
                ]);
            }

            // Если просто текстовое сообщение - показываем подтверждение
            return $this->confirmAd($chatId, $data);
        }

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => '⚠️ Непонятная команда. Используйте кнопки.',
        ]);
    }

    private function askForFile($chatId):Message
    {
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "📸 Отправьте фото или файлы для объявления.\n" .
                "Или нажмите 'Пропустить'",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '⏭ Пропустить']],
                    [['text' => '❌ Отмена']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    private function askForAudience($chatId):Message
    {
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "👥 Кому отправить объявление?\n\n" .
                "Выберите аудиторию:",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '👥 Всем'], ['text' => '🍳 Сотрудникам кухни']],
                    [['text' => '🛎 Сотрудникам зала']],
                    [['text' => '⬅️ Назад'], ['text' => '❌ Отмена']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    private function confirmAd($chatId, $data):Message
    {
        // Получаем название роли для отображения
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

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '✅ Опубликовать'], ['text' => '👥 Изменить получателей']],
                    [['text' => '✏️ Изменить текст']],
                    [['text' => '❌ Отмена']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    /**
     * Обработка всех файлов в сообщении
     */
    private function processAllFiles($message)
    {
        $files = [];

        // 1. Обрабатываем фото
        if (isset($message->photo) && !empty($message->photo)) {
            $photoArray = $message->photo;
            $lastKey = array_key_last($photoArray);
            $photo = $photoArray[$lastKey];

            $fileInfo = $this->processPhoto($photo);
            if ($fileInfo) {
                $files[] = $fileInfo;
            }
        }

        // 2. Обрабатываем документы
        if (isset($message->document)) {
            $fileInfo = $this->processDocument($message->document);
            if ($fileInfo) {
                $files[] = $fileInfo;
            }
        }

        return $files;
    }

    private function processPhoto($photo):array|null
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

    private function processDocument($document):array|null
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

    private function downloadAndSaveFile($fileId, $fileName, $mimeType):array|null
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

    private function publishAd($chatId, $data):Message
    {
        DB::beginTransaction();
        try {
            $telegramUser = session("ad_user_{$chatId}") ?? null;

            // Создаем объявление с role_id
            $ad = Advertisement::create([
                'content' => $data['text'],
                'telegram_author_name' => $telegramUser['telegram_username'] ?? null,
                'status' => 'active',
                'published_at' => now(),
                'role_id' => $data['role_id'] ?? 2, // По умолчанию 2 (Всем)
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

            // Очищаем сессию
            session()->forget("ad_{$chatId}");
            session()->forget("ad_user_{$chatId}");

            // Получаем название роли для отображения
            $roleLabels = [
                2 => 'всем',
                3 => 'сотрудникам кухни',
                4 => 'сотрудникам зала'
            ];

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Объявление успешно опубликовано!\n\n" .
                    "🆔 ID: {$ad->id}\n" .
                    "👥 Отправлено: " . ($roleLabels[$data['role_id'] ?? 2] ?? 'всем') . "\n" .
                    "📅 Дата: " . now()->format('d.m.Y H:i'),
                'reply_markup' => [
                    'keyboard' => [
                        [['text' => '🏠 На главную']],
                    ],
                    'remove_keyboard' => true
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка публикации объявления: ' . $e->getMessage(), [
                'chat_id' => $chatId,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Произошла ошибка при публикации. Попробуйте позже.',
                'reply_markup' => ['remove_keyboard' => true],
            ]);
        }
    }
}

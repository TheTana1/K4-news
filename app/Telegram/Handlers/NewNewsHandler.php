<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use App\Services\TelegramService;
use App\Services\UserRegistrationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NewNewsHandler
{
    public function __construct(
        readonly UserRegistrationService $userRegistrationService,
        readonly TelegramService         $telegramService,
    )
    {
    }

    public function handle($chatId, $userDb): bool
    {
        if (!$chatId) {
            return $this->telegramService->sendMessage($chatId, '❌ Ошибка чата');
        }
        if (!$userDb) {
            return $this->telegramService->sendMessage($chatId, '❌ Ошибка базы данных');
        }

        session(["news_user_{$chatId}" => [
            'name' => $userDb->name,
            'telegram_id' => $userDb->telegram_id ?? null,
            'telegram_username' => $userDb->telegram_username ?? null,
            'user_id' => $userDb->id,
        ]]);

        $sessionKey = "news_{$chatId}";
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
            if ($data['step'] == 2) {
                return $this->askForPhotos($chatId);
            }
            if ($data['step'] == 3) {
                return $this->askForAudience($chatId);
            }
            if ($data['step'] == 4) {
                return $this->confirmNews($chatId, $data);
            }
        }

        session([$sessionKey => ['step' => 1, 'photos' => []]]);

        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "📝 Напишите текст новости.\n\nДля отмены нажмите /cancel",
            [[['text' => '❌ Отмена']]]
        );
    }

    public function handleMessage($message): bool
    {
        $chatId = $message->chat->id ?? null;
        if (!$chatId) return false;

        $text = $message->text ?? '';
        $sessionKey = "news_{$chatId}";
        $data = session($sessionKey, ['step' => 1, 'photos' => []]);

        if ($text === '❌ Отмена' || $text === '/cancel') {
            session()->forget($sessionKey);
            session()->forget("news_user_{$chatId}");

            return $this->telegramService->sendWithRemoveKeyboard(
                $chatId,
                '❌ Создание новости отменено.'
            );
        }

        if ($data['step'] == 1) {
            if (empty($text)) {
                return $this->telegramService->sendMessage(
                    $chatId,
                    '❌ Текст новости не может быть пустым. Попробуйте снова.'
                );
            }

            $data['text'] = $text;
            $data['step'] = 2;
            session([$sessionKey => $data]);

            return $this->askForPhotos($chatId);
        }

        if ($data['step'] == 2) {
            if ($text === '✅ Готово') {
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId);
            }

            if ($text === '⏭ Пропустить') {
                $data['photos'] = [];
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId);
            }

            if (isset($message->photo) && !empty($message->photo)) {
                $photoInfo = $this->processPhoto($message->photo);

                if ($photoInfo) {
                    $data['photos'][] = $photoInfo;
                    session([$sessionKey => $data]);

                    $count = count($data['photos']);
                    $msg = "✅ Загружено фото: {$count}\n\n";
                    $msg .= "Отправьте еще фото или нажмите 'Готово' для продолжения.";

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
            }

            if (isset($message->document)) {
                return $this->telegramService->sendMessage(
                    $chatId,
                    '❌ Для новости можно отправлять только фотографии. Пожалуйста, отправьте фото.'
                );
            }

            return $this->telegramService->sendMessage(
                $chatId,
                '❌ Пожалуйста, отправьте фото или нажмите "Пропустить".'
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
                return $this->confirmNews($chatId, $data);
            }

            if ($text === '⬅️ Назад') {
                $data['step'] = 2;
                session([$sessionKey => $data]);
                return $this->askForPhotos($chatId);
            }

            return $this->askForAudience($chatId);
        }

        if ($data['step'] == 4) {
            if ($text === '✅ Опубликовать') {
                return $this->publishNews($chatId, $data);
            }

            if ($text === '✏️ Изменить текст') {
                $data['step'] = 1;
                session([$sessionKey => $data]);

                return $this->telegramService->sendWithKeyboard(
                    $chatId,
                    "✏️ Введите новый текст новости:",
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
                session()->forget("news_user_{$chatId}");

                return $this->telegramService->sendWithRemoveKeyboard(
                    $chatId,
                    '❌ Создание новости отменено.'
                );
            }

            return $this->confirmNews($chatId, $data);
        }

        return $this->telegramService->sendMessage(
            $chatId,
            '⚠️ Непонятная команда. Используйте кнопки.'
        );
    }

    private function askForPhotos($chatId): bool
    {
        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "📸 Отправьте фотографии для новости.\n" .
            "Можно отправить несколько фото по одному.\n" .
            "Когда закончите, нажмите 'Готово'",
            [
                [['text' => '✅ Готово']],
                [['text' => '⏭ Пропустить']],
                [['text' => '❌ Отмена']],
            ]
        );
    }

    private function askForAudience($chatId): bool
    {
        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "👥 Кому отправить новость?\n\nВыберите аудиторию:",
            [
                [['text' => '👥 Всем'], ['text' => '🍳 Сотрудникам кухни']],
                [['text' => '🛎 Сотрудникам зала']],
                [['text' => '⬅️ Назад'], ['text' => '❌ Отмена']],
            ]
        );
    }

    private function confirmNews($chatId, $data): bool
    {
        $roleLabels = [
            2 => '👥 Всем',
            3 => '🍳 Сотрудникам кухни',
            4 => '🛎 Сотрудникам зала'
        ];

        $text = "✅ Проверьте новость:\n\n";
        $text .= "📝 Текст:\n{$data['text']}\n\n";
        $text .= "👥 Получатели: " . ($roleLabels[$data['role_id'] ?? 2] ?? '👥 Всем') . "\n\n";

        if (!empty($data['photos']) && is_array($data['photos'])) {
            $text .= "📸 Фото (всего: " . count($data['photos']) . "):\n";
            foreach ($data['photos'] as $index => $photo) {
                $text .= "  " . ($index + 1) . ". {$photo['file_name']}\n";
            }
        } else {
            $text .= "📸 Без фото\n";
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

    private function processPhoto($photo): array|null
    {
        try {
            if (is_array($photo) && !empty($photo)) {
                $photoArray = $photo;
                $lastKey = array_key_last($photoArray);
                $photo = $photoArray[$lastKey];
            }

            $fileId = $photo->file_id;
            $fileName = 'photo_' . time() . '_' . uniqid() . '.jpg';
            $mimeType = 'image/jpeg';

            return $this->downloadAndSavePhoto($fileId, $fileName, $mimeType);
        } catch (\Exception $e) {
            Log::error('Ошибка обработки фото: ' . $e->getMessage());
            return null;
        }
    }

    private function downloadAndSavePhoto($fileId, $fileName, $mimeType): array|null
    {
        try {
            $file = TeleBot::getFile(['file_id' => $fileId]);
            $fileContent = file_get_contents($file->url(config('telebot.bots.default.token')));

            if ($fileContent === false) {
                Log::error('Не удалось скачать фото: ' . $fileId);
                return null;
            }

            $lastNews = News::query()->latest('id')->first();
            $nextId = $lastNews ? $lastNews->id + 1 : 1;

            $path = 'news/' . $nextId . '/' . $fileName;
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
            Log::error('Ошибка скачивания фото: ' . $e->getMessage());
            return null;
        }
    }

    private function publishNews($chatId, $data): bool
    {
        DB::beginTransaction();
        try {
            $telegramUser = session("news_user_{$chatId}") ?? null;

            $news = News::create([
                'content' => $data['text'],
                'user_id' => User::where('telegram_username', $telegramUser['telegram_username'])->value('id') ?? null,
                'status' => 'active',
                'published_at' => now(),
                'role_id' => $data['role_id'] ?? 2,
            ]);

            if (!empty($data['photos']) && is_array($data['photos'])) {
                foreach ($data['photos'] as $photoData) {
                    if (empty($photoData['file_path']) || empty($photoData['file_name'])) {
                        continue;
                    }

                    $oldPath = $photoData['file_path'];
                    $newPath = 'news/' . $news->id . '/' . $photoData['file_name'];

                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->move($oldPath, $newPath);
                        $photoData['file_path'] = $newPath;
                    }

                    $news->files()->create($photoData);
                }
            }

            DB::commit();

            Cache::tags(['news-index'])->flush();
            Cache::tags(['dashboard'])->flush();

            session()->forget("news_{$chatId}");
            session()->forget("news_user_{$chatId}");

            $roleLabels = [
                2 => 'всем',
                3 => 'сотрудникам кухни',
                4 => 'сотрудникам зала'
            ];

            $text = "✅ <b>Новость успешно опубликована!</b>\n\n" .
                "🆔 ID: {$news->id}\n" .
                "👥 Отправлено: " . ($roleLabels[$data['role_id'] ?? 2] ?? 'всем') . "\n" .
                "📅 Дата: " . now()->format('d.m.Y H:i');

            return $this->telegramService->sendHtmlWithKeyboard(
                $chatId,
                $text,
                [
                    [['text' => '🏠 На главную']]
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка публикации новости: ' . $e->getMessage(), [
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

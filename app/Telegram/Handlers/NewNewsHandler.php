<?php
// app/Telegram/Handlers/NewNewsHandler.php

namespace App\Telegram\Handlers;

use App\Services\UserRegistrationService;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NewNewsHandler
{
    public function __construct(readonly UserRegistrationService $userRegistrationService)
    {
    }

    public function handle($update)
    {
        $chatId = $update->message->chat->id ?? null;
        if (!$chatId) return;

        $telegramUser = $update->message->from ?? null;
        if (!$telegramUser) {
            Log::error('Telegram user not found in update');
            return;
        }

        // Регистрируем пользователя
        $userDb = $this->userRegistrationService->registerFromTelegram($telegramUser);

        if (!$userDb) {
            Log::error('Failed to register user', ['telegram_id' => $telegramUser->id]);
            TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Ошибка регистрации. Попробуйте позже.'
            ]);
            return;
        }

        session(["news_user_{$chatId}" => [
            'name' => $userDb->name,
            'telegram_id' => $telegramUser->id,
            'telegram_username' => $telegramUser->username ?? null,
            'user_id' => $userDb->id,
        ]]);

        // Проверяем, есть ли незавершённая новость
        $sessionKey = "news_{$chatId}";
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
            if ($data['step'] == 2) {
                return $this->askForPhotos($chatId);
            }
            if ($data['step'] == 3) {
                return $this->confirmNews($chatId, $data);
            }
        }

        // Начинаем новый процесс
        session([$sessionKey => ['step' => 1, 'photos' => []]]);

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "📝 Напишите текст новости.\n\n" .
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
        if (!$chatId) return;

        $text = $message->text ?? '';
        $sessionKey = "news_{$chatId}";
        $data = session($sessionKey, ['step' => 1, 'photos' => []]);

        // Отмена
        if ($text === '❌ Отмена' || $text === '/cancel') {
            session()->forget($sessionKey);
            session()->forget("news_user_{$chatId}");

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Создание новости отменено.',
                'reply_markup' => ['remove_keyboard' => true],
            ]);
        }

        // Шаг 1: Получаем текст
        if ($data['step'] == 1) {
            if (empty($text)) {
                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❌ Текст новости не может быть пустым. Попробуйте снова.',
                ]);
            }

            $data['text'] = $text;
            $data['step'] = 2;
            session([$sessionKey => $data]);

            return $this->askForPhotos($chatId);
        }

        // Шаг 2: Получаем фото
        if ($data['step'] == 2) {
            // ✅ Обработка кнопки "Готово"
            if ($text === '✅ Готово') {
                if (empty($data['photos'])) {
                    return TeleBot::sendMessage([
                        'chat_id' => $chatId,
                        'text' => '❌ Добавьте хотя бы одно фото или нажмите "Пропустить".',
                    ]);
                }

                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->confirmNews($chatId, $data);
            }

            if ($text === '⏭ Пропустить') {
                $data['photos'] = [];
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->confirmNews($chatId, $data);
            }

            // ✅ Проверяем наличие фото (ТОЛЬКО ФОТО!)
            if (isset($message->photo) && !empty($message->photo)) {
                $photoInfo = $this->processPhoto($message->photo);

                if ($photoInfo) {
                    $data['photos'][] = $photoInfo;
                    session([$sessionKey => $data]);

                    $count = count($data['photos']);
                    $text = "✅ Загружено фото: {$count}\n\n";
                    $text .= "Отправьте еще фото или нажмите 'Готово' для продолжения.";

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
            }

            // ❌ Если прислали документ вместо фото
            if (isset($message->document)) {
                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❌ Для новости можно отправлять только фотографии. Пожалуйста, отправьте фото.',
                ]);
            }

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Пожалуйста, отправьте фото или нажмите "Пропустить".',
            ]);
        }

        // Шаг 3: Подтверждение
        if ($data['step'] == 3) {
            if ($text === '✅ Опубликовать') {
                return $this->publishNews($chatId, $data);
            }

            if ($text === '✏️ Изменить текст') {
                $data['step'] = 1;
                session([$sessionKey => $data]);

                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✏️ Введите новый текст новости:",
                    'reply_markup' => [
                        'keyboard' => [
                            [['text' => '❌ Отмена']],
                        ],
                        'resize_keyboard' => true,
                    ],
                ]);
            }

            if ($text === '❌ Отмена') {
                session()->forget($sessionKey);
                session()->forget("news_user_{$chatId}");

                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❌ Создание новости отменено.',
                    'reply_markup' => ['remove_keyboard' => true],
                ]);
            }

            // Если просто текстовое сообщение - показываем подтверждение
            return $this->confirmNews($chatId, $data);
        }

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => '⚠️ Непонятная команда. Используйте кнопки.',
        ]);
    }

    /**
     * Запрос на отправку фото
     */
    private function askForPhotos($chatId)
    {
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "📸 Отправьте фотографии для новости.\n" .
                "Можно отправить несколько фото по одному.\n" .
                "Когда закончите, нажмите 'Готово'",
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

    /**
     * Подтверждение новости
     */
    private function confirmNews($chatId, $data)
    {
        $text = "✅ Проверьте новость:\n\n";
        $text .= "📝 Текст:\n{$data['text']}\n\n";

        if (!empty($data['photos']) && is_array($data['photos'])) {
            $text .= "📸 Фото (всего: " . count($data['photos']) . "):\n";
            foreach ($data['photos'] as $index => $photo) {
                $text .= "  " . ($index + 1) . ". {$photo['file_name']}\n";
            }
        } else {
            $text .= "📸 Без фото\n";
        }

        $text .= "\nПодтвердите публикацию или отредактируйте.";

        $replyMarkup = [
            'keyboard' => [
                [
                    ['text' => '✅ Опубликовать'],
                    ['text' => '✏️ Изменить текст']
                ],
                [
                    ['text' => '❌ Отмена']
                ]
            ],
            'resize_keyboard' => true,
        ];

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $replyMarkup,
        ]);
    }

    /**
     * Обработка фото
     */
    private function processPhoto($photo)
    {
        try {
            // Берем самое большое фото (последнее в массиве)
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

    /**
     * Скачивание и сохранение фото
     */
    private function downloadAndSavePhoto($fileId, $fileName, $mimeType)
    {
        try {
            $file = TeleBot::getFile(['file_id' => $fileId]);
            $fileContent = file_get_contents($file->url(config('telebot.bots.default.token')));

            if ($fileContent === false) {
                Log::error('Не удалось скачать фото: ' . $fileId);
                return null;
            }

            // Получаем следующий ID для папки
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

    /**
     * Публикация новости
     */
    private function publishNews($chatId, $data)
    {
        try {
            $telegramUser = session("news_user_{$chatId}") ?? null;

            $news = News::create([
                'content' => $data['text'],
                'telegram_author_name' => $telegramUser['name'] ?? null,
                'status' => 'active',
                'published_at' => now(),
            ]);

            // Сохраняем фото
            if (!empty($data['photos']) && is_array($data['photos'])) {
                foreach ($data['photos'] as $photoData) {
                    if (empty($photoData['file_path']) || empty($photoData['file_name'])) {
                        continue;
                    }

                    // Перемещаем фото в папку с ID новости
                    $oldPath = $photoData['file_path'];
                    $newPath = 'news/' . $news->id . '/' . $photoData['file_name'];

                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->move($oldPath, $newPath);
                        $photoData['file_path'] = $newPath;
                    }

                    $news->files()->create($photoData);
                }
            }

            // Очищаем сессию
            session()->forget("news_{$chatId}");
            session()->forget("news_user_{$chatId}");

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Новость успешно опубликована!\n\n" .
                    "🆔 ID: {$news->id}\n" .
                    "📅 Дата: " . now()->format('d.m.Y H:i'),
                'reply_markup' => [
                    'keyboard' => [
                        [['text' => '🏠 На главную']],
                    ],
                    'remove_keyboard' => true],
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка публикации новости: ' . $e->getMessage(), [
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

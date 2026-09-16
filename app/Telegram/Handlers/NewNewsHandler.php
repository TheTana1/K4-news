<?php
// app/Telegram/Handlers/NewNewsHandler.php

namespace App\Telegram\Handlers;

use App\Services\UserRegistrationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Objects\Message;

class NewNewsHandler
{
    public function __construct(readonly UserRegistrationService $userRegistrationService)
    {
    }

    public function handle($chatId, $userDb):Message
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

        session(["news_user_{$chatId}" => [
            'name' => $userDb->name,
            'telegram_id' => $userDb->telegram_id ?? null,
            'telegram_username' => $userDb->telegram_username ?? null,
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
                return $this->askForAudience($chatId, $data);
            }
            if ($data['step'] == 4) {
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
        if (!$chatId) return false;

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
                'reply_markup' => [
                    'keyboard' => [
                        [['text' => '🏠 На главную']],
                    ],
                    'remove_keyboard' => true],
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
            if ($text === '✅ Готово') {
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId, $data);
            }

            if ($text === '⏭ Пропустить') {
                $data['photos'] = [];
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId, $data);
            }

            // Проверяем наличие фото
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
                            'remove_keyboard' => true,
                        ],
                    ]);
                }
            }

            // Если прислали документ вместо фото
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
                return $this->confirmNews($chatId, $data);
            }

            // Если нажали "Назад"
            if ($text === '⬅️ Назад') {
                $data['step'] = 2;
                session([$sessionKey => $data]);
                return $this->askForPhotos($chatId);
            }

            // Если просто текст - показываем выбор снова
            return $this->askForAudience($chatId, $data);
        }

        // Шаг 4: Подтверждение
        if ($data['step'] == 4) {
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

            if ($text === '👥 Изменить получателей') {
                $data['step'] = 3;
                session([$sessionKey => $data]);
                return $this->askForAudience($chatId, $data);
            }

            if ($text === '❌ Отмена') {
                session()->forget($sessionKey);
                session()->forget("news_user_{$chatId}");

                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❌ Создание новости отменено.',
                    'reply_markup' => [
                        'keyboard' => [
                            [['text' => '🏠 На главную']],
                        ],
                        'remove_keyboard' => true],
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
    private function askForPhotos($chatId):Message
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
                'remove_keyboard' => true,
            ],
        ]);
    }

    /**
     * Запрос на выбор аудитории
     */
    private function askForAudience($chatId):Message
    {
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "👥 Кому отправить новость?\n\n" .
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

    /**
     * Подтверждение новости
     */
    private function confirmNews($chatId, $data):Message
    {
        // Получаем название роли для отображения
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

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '✅ Опубликовать'], ['text' => '👥 Изменить получателей']],
                    [['text' => '✏️ Изменить текст']],
                    [['text' => '❌ Отмена']],
                ],
                'remove_keyboard' => true,
            ],
        ]);
    }

    /**
     * Обработка фото
     */
    private function processPhoto($photo):array|null
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
    private function downloadAndSavePhoto($fileId, $fileName, $mimeType):array|null
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
    private function publishNews($chatId, $data):Message
    {
        DB::beginTransaction();
        try {
            $telegramUser = session("news_user_{$chatId}") ?? null;

            $news = News::create([
                'content' => $data['text'],
                'telegram_author_name' => $telegramUser['telegram_username'] ?? null,
                'status' => 'active',
                'published_at' => now(),
                'role_id' => $data['role_id'] ?? 2, // По умолчанию 2 (Всем)
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

            DB::commit();

            Cache::tags(['news-index'])->flush();
            Cache::tags(['dashboard'])->flush();

            // Очищаем сессию
            session()->forget("news_{$chatId}");
            session()->forget("news_user_{$chatId}");

            // Получаем название роли для отображения
            $roleLabels = [
                2 => 'всем',
                3 => 'сотрудникам кухни',
                4 => 'сотрудникам зала'
            ];

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Новость успешно опубликована!\n\n" .
                    "🆔 ID: {$news->id}\n" .
                    "👥 Отправлено: " . ($roleLabels[$data['role_id'] ?? 2] ?? 'всем') . "\n" .
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
                'reply_markup' => [
                    'keyboard' => [
                        [['text' => '🏠 На главную']],
                    ],
                    'remove_keyboard' => true],
            ]);
        }
    }
}

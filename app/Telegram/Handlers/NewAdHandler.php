<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramBotService;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NewAdHandler
{
    public function __construct(private readonly TelegramBotService $telegramBotService)
    {
    }

    public function handle($update)
    {
        $chatId = $update->message->chat->id ?? null;
        if (!$chatId) return;

        $this->ensureUserExists($update);

        $sessionKey = "ad_{$chatId}";
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
            return match ($data['step']) {
                2 => $this->askForFile($chatId),
                3 => $this->confirmAd($chatId, $data),
                default => $this->startNewAd($chatId),
            };
        }

        return $this->startNewAd($chatId);
    }

    public function handleMessage($update, $message)
    {
        $chatId = $update->message->chat->id ?? null;
        if (!$chatId) return;

        $text = $message->text ?? '';
        $sessionKey = "ad_{$chatId}";
        $data = session($sessionKey, ['step' => 1, 'files' => []]);

        // === Навигация ===
        return match (true) {
            $text === '🔙 Назад в меню' => $this->goBackToMenu($chatId, $sessionKey),
            $text === '❌ Отмена' => $this->cancelCreation($chatId, $sessionKey),
            $text === '✏️ Изменить текст' => $this->editText($chatId, $sessionKey, $data),
            $text === '✅ Опубликовать' => $this->publishAd($chatId, $data),
            $text === '📎 Добавить ещё файл' => $this->askForFile($chatId),
            $text === '✅ Готово' || $text === '⏭ Пропустить' => $this->finishFileUpload($chatId, $sessionKey, $data),
            $data['step'] == 1 => $this->handleTextStep($chatId, $sessionKey, $data, $text),
            $data['step'] == 2 => $this->handleFileStep($chatId, $sessionKey, $data, $update, $text),
            default => $this->sendUnknownCommand($chatId),
        };
    }

    private function ensureUserExists($update): void
    {
        $telegramUser = $update->message->from ?? null;
        if ($telegramUser && isset($telegramUser->id)) {
            $user = User::where('telegram_id', $telegramUser->id)->first();
            if (!$user) {
                $this->telegramBotService->registerUser($update);
            }
        }
    }

    private function startNewAd($chatId)
    {
        $sessionKey = "ad_{$chatId}";
        session([$sessionKey => ['step' => 1, 'files' => []]]);

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "📝 Напишите текст вашего объявления.\n\n" .
                "Опишите товар или услугу, укажите цену и контакты.",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '🔙 Назад в меню']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    private function goBackToMenu($chatId, $sessionKey)
    {
        session()->forget($sessionKey);
        return $this->telegramBotService->showMainMenu($chatId);
    }

    private function cancelCreation($chatId, $sessionKey)
    {
        session()->forget($sessionKey);
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => '❌ Создание объявления отменено.',
            'reply_markup' => $this->getMainMenuKeyboard(),
        ]);
    }

    private function editText($chatId, $sessionKey, &$data)
    {
        $data['step'] = 1;
        session([$sessionKey => $data]);
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "✏️ Введите новый текст объявления:",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '🔙 Назад в меню']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    private function handleTextStep($chatId, $sessionKey, &$data, $text)
    {
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

    private function handleFileStep($chatId, $sessionKey, &$data, $update, $text)
    {
        // Получаем файл из обновления
        $fileInfo = $this->getFileInfoFromUpdate($update);

        if ($fileInfo) {
            $data['files'][] = $fileInfo;
            $data['step'] = 2;
            session([$sessionKey => $data]);

            $filesCount = count($data['files']);
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Файл #{$filesCount} загружен!\n\n" .
                    "📎 Всего файлов: {$filesCount}\n\n" .
                    "Вы можете отправить ещё файлы или нажать 'Готово' для перехода к подтверждению.",
                'reply_markup' => [
                    'keyboard' => [
                        [['text' => '📎 Добавить ещё файл']],
                        [['text' => '✅ Готово']],
                        [['text' => '🔙 Назад в меню'], ['text' => '❌ Отмена']],
                    ],
                    'resize_keyboard' => true,
                ],
            ]);
        }

        // Если это текст, но не команда
        if (!empty($text)) {
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Пожалуйста, отправьте фото или документ, или нажмите "Готово".',
            ]);
        }

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => '❌ Пожалуйста, отправьте фото, документ или нажмите "Готово".',
        ]);
    }

    private function finishFileUpload($chatId, $sessionKey, &$data)
    {
        $data['step'] = 3;
        session([$sessionKey => $data]);
        return $this->confirmAd($chatId, $data);
    }

    /**
     * Получение информации о файле из обновления
     */
    private function getFileInfoFromUpdate($update)
    {
        $message = $update->message ?? null;
        if (!$message) {
            return null;
        }

        // Проверяем фото
        if (isset($message->photo) && !empty($message->photo)) {
            return $this->processPhoto($message);
        }

        // Проверяем документ
        if (isset($message->document)) {
            return $this->processDocument($message);
        }

        return null;
    }

    private function askForFile($chatId)
    {
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "📸 Отправьте фото или файл для объявления.\n" .
                "Поддерживаются форматы: JPG, PNG, PDF, DOC, DOCX, TXT и другие.\n\n" .
                "Вы можете отправить несколько файлов по очереди.\n" .
                "Когда закончите, нажмите 'Готово'.",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '✅ Готово']],
                    [['text' => '🔙 Назад в меню'], ['text' => '❌ Отмена']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    private function confirmAd($chatId, $data)
    {
        $text = "✅ Проверьте объявление:\n\n";
        $text .= "📝 Текст:\n{$data['text']}\n\n";

        $filesCount = count($data['files'] ?? []);
        $text .= "📎 Количество файлов: {$filesCount}\n";

        if ($filesCount > 0) {
            $text .= "\n📋 Список файлов:\n";
            foreach ($data['files'] as $index => $file) {
                $text .= ($index + 1) . ". {$file['file_name']} (" . $this->formatFileSize($file['file_size']) . ")\n";
            }
        } else {
            $text .= "📎 Без файлов\n";
        }

        $text .= "\nПодтвердите публикацию или измените текст.";

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '✅ Опубликовать'], ['text' => '✏️ Изменить текст']],
                    [['text' => '📎 Добавить ещё файл']],
                    [['text' => '🔙 Назад в меню'], ['text' => '❌ Отмена']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    private function processPhoto($message)
    {
        try {
            $photos = $message->photo;
            $photo = $photos[array_key_last($photos)];
            return $this->downloadAndSaveFile(
                $photo->file_id,
                'photo_' . time() . '_' . rand(1000, 9999) . '.jpg',
                'image/jpeg'
            );
        } catch (\Exception $e) {
            Log::error('Ошибка обработки фото: ' . $e->getMessage());
            return null;
        }
    }

    private function processDocument($message)
    {
        try {
            $document = $message->document;
            $originalName = $document->file_name ?? 'document_' . time();
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $name = pathinfo($originalName, PATHINFO_FILENAME);
            $uniqueName = $name . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

            return $this->downloadAndSaveFile(
                $document->file_id,
                $uniqueName,
                $document->mime_type ?? 'application/octet-stream',
                $originalName
            );
        } catch (\Exception $e) {
            Log::error('Ошибка обработки документа: ' . $e->getMessage());
            return null;
        }
    }

    private function downloadAndSaveFile($fileId, $fileName, $mimeType, $originalName = null)
    {
        try {
            $file = TeleBot::getFile(['file_id' => $fileId]);
            $content = TeleBot::downloadFile($file->file_path);

            $storagePath = 'telegram/ads/' . date('Y/m/d/');
            $fullPath = $storagePath . $fileName;

            Storage::disk('public')->put($fullPath, $content);

            return [
                'file_path' => $fullPath,
                'file_name' => $originalName ?? $fileName,
                'file_size' => $file->file_size ?? 0,
                'mime_type' => $mimeType,
                'disk' => 'public',
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка скачивания файла: ' . $e->getMessage());
            return null;
        }
    }

    private function publishAd($chatId, $data)
    {
        try {
            if (empty($data['text'])) {
                return TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❌ Текст объявления отсутствует. Начните заново через /new_ad',
                ]);
            }

            $user = User::where('telegram_id', $chatId)->first();

            $adData = [
                'content' => $data['text'],
                'telegram_author_name' => $user->name ?? null,
                'status' => true,
                'published_at' => now(),
            ];

            if (!empty($data['files'])) {
                $firstFile = $data['files'][0];
                $adData['file_path'] = $firstFile['file_path'];
                $adData['file_name'] = $firstFile['file_name'];
                $adData['file_size'] = $firstFile['file_size'];
                $adData['mime_type'] = $firstFile['mime_type'];
                $adData['disk'] = $firstFile['disk'];
            }

            $ad = Advertisement::create($adData);

            foreach ($data['files'] as $fileData) {
                $ad->files()->create($fileData);
            }

            session()->forget("ad_{$chatId}");

            $filesCount = count($data['files']);
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Объявление успешно опубликовано!\n\n" .
                    "ID: {$ad->id}\n" .
                    "📎 Файлов загружено: {$filesCount}\n\n" .
                    "Вернуться в главное меню: /start",
                'reply_markup' => $this->getMainMenuKeyboard(),
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка публикации объявления: ' . $e->getMessage());
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Произошла ошибка при публикации. Попробуйте позже.',
                'reply_markup' => [
                    'keyboard' => [
                        [['text' => '🔙 Назад в меню']],
                    ],
                    'resize_keyboard' => true,
                ],
            ]);
        }
    }

    private function formatFileSize($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getMainMenuKeyboard(): array
    {
        return [
            'keyboard' => [
                [['text' => '📝 Создать объявление']],
                [['text' => '⭐ Оставить отзыв']],
                [['text' => '❓ Помощь']],
            ],
            'resize_keyboard' => true,
        ];
    }

    private function sendUnknownCommand($chatId)
    {
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => '⚠️ Непонятная команда.',
        ]);
    }
}

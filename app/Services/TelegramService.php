<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Laravel\TeleBot;
use function PHPUnit\Framework\isResource;

class TelegramService
{
    /**
     * Отправить HTML-сообщение.
     */
    public function sendHtmlMessage(?string $chatId, string $text, array $options = []): bool
    {
        return $this->sendHtml($chatId, $text, $options);
    }

    /**
     * Отправить обычное сообщение.
     */
    public function sendMessage(?string $chatId, string $text, array $options = []): bool
    {
        return $this->send($chatId, $text, $options);
    }

    /**
     * Отправить HTML-сообщение с кнопками (reply keyboard).
     */
    public function sendHtmlWithKeyboard(?string $chatId, string $text, array $keyboard, array $options = []): bool
    {
        return $this->sendHtml($chatId, $text, array_merge([
            'reply_markup' => [
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
            ],
        ], $options));
    }

    /**
     * Отправить HTML-сообщение и убрать клавиатуру.
     */
    public function sendHtmlWithRemoveKeyboard(?string $chatId, string $text, array $options = []): bool
    {
        return $this->sendHtml($chatId, $text, array_merge([
            'reply_markup' => [
                'remove_keyboard' => true,
            ],
        ], $options));
    }

    /**
     * Отправить HTML-сообщение с inline-кнопками.
     */
    public function sendHtmlWithInlineKeyboard(?string $chatId, string $text, array $inlineKeyboard, array $options = []): bool
    {
        return $this->sendHtml($chatId, $text, array_merge([
            'reply_markup' => [
                'inline_keyboard' => $inlineKeyboard,
            ],
        ], $options));
    }

    /**
     * Отправить сообщение с кнопками (reply keyboard).
     *
     * @param array $keyboard Массив рядов кнопок:
     *   [
     *     [['text' => 'Кнопка 1'], ['text' => 'Кнопка 2']],
     *     [['text' => 'Кнопка 3']],
     *   ]
     */
    public function sendWithKeyboard(?string $chatId, string $text, array $keyboard, array $options = []): bool
    {
        return $this->send($chatId, $text, array_merge([
            'reply_markup' => [
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
            ],
        ], $options));
    }

    /**
     * Отправить сообщение и убрать клавиатуру.
     */
    public function sendWithRemoveKeyboard(?string $chatId, string $text, array $options = []): bool
    {
        return $this->send($chatId, $text, array_merge([
            'reply_markup' => [
                'remove_keyboard' => true,
            ],
        ], $options));
    }

    /**
     * Отправить сообщение с inline-кнопками.
     *
     * @param array $inlineKeyboard Массив рядов inline-кнопок:
     *   [
     *     [['text' => 'Сайт', 'url' => 'https://...']],
     *     [['text' => 'Нажми', 'callback_data' => 'action']],
     *   ]
     */
    public function sendWithInlineKeyboard(?string $chatId, string $text, array $inlineKeyboard, array $options = []): bool
    {
        return $this->send($chatId, $text, array_merge([
            'reply_markup' => [
                'inline_keyboard' => $inlineKeyboard,
            ],
        ], $options));
    }

    /**
     * Базовый метод отправки обычного сообщения.
     */
    protected function send(?string $chatId, string $text, array $options = []): bool
    {
        return $this->dispatch($chatId, $text, $options);
    }

    /**
     * Базовый метод отправки HTML-сообщения.
     */
    protected function sendHtml(?string $chatId, string $text, array $options = []): bool
    {
        return $this->dispatch($chatId, $text, array_merge([
            'parse_mode' => 'HTML',
        ], $options));
    }

    /**
     * Общий метод отправки. Сюда приходят уже собранные опции.
     */
    protected function dispatch(?string $chatId, string $text, array $options = []): bool
    {
        if (empty($chatId)) {
            Log::warning('Telegram send skipped: empty chat_id', [
                'text' => mb_substr($text, 0, 100),
            ]);
            return false;
        }

        try {
            TeleBot::sendMessage(array_merge([
                'chat_id' => $chatId,
                'text' => $text,
            ], $options));

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram send failed: ' . $e->getMessage(), [
                'chat_id' => $chatId,
                'text' => mb_substr($text, 0, 100),
            ]);
            return false;
        }
    }

    public function sendPhoto(string $file_path, ?string $chatId)
    {
        try {
            $handle = fopen(storage_path('/app/public/' . $file_path), 'rb');
            TeleBot::sendPhoto([
                'chat_id' => $chatId,
                'photo' => $handle,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Telegram send failed: ' . $e->getMessage(), ['chat_id' => $chatId,]);
return false;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function sendDocument(string $file_path, ?string $chatId)
    {
        try {
            $handle = fopen(storage_path('/app/public/' . $file_path), 'rb');
            TeleBot::sendDocument([
                'chat_id' => $chatId,
                'document' => $handle,
            ]);

        } catch
        (\Exception $e) {
            Log::error('Telegram send failed: ' . $e->getMessage(), [
                'chat_id' => $chatId,
            ]);

        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }
}

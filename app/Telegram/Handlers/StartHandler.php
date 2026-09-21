<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramService;

class StartHandler
{
    public function __construct(
        readonly TelegramService $telegramService,
    ) {
    }

    public function handle($chatId): bool
    {
        if (!$chatId) {
            return $this->telegramService->sendMessage($chatId, '❌ Ошибка чата');
        }

        $text = "👋 Привет!\n\n";
        $text .= "Я бот для публикации объявлений и отзывов.\n\n";
        $text .= "📌 Доступные команды:\n";
        $text .= "/new_ad - Создать объявление\n";
        $text .= "/new_news - Создать новость\n";
        $text .= "/help - Помощь\n";

        return $this->telegramService->sendWithKeyboard(
            $chatId,
            $text,
            [
                [['text' => '📝 Новое объявление']],
                [['text' => '📝 Новая новость']],
                [['text' => '❓ Помощь']],
            ]
        );
    }
}

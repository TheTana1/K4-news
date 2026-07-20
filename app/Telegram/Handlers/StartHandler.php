<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramBotService;
use WeStacks\TeleBot\Laravel\TeleBot;

class StartHandler
{
    public function __construct(private readonly TelegramBotService $telegramBotService)
    {
    }

    public function handle($update)
    {
        $chatId = $update->message->chat->id ?? null;
        if (empty($chatId)) return false;

        $this->telegramBotService->registerUser($update);

        return $this->showMainMenu($chatId);
    }

    public function showMainMenu($chatId)
    {
        $text = "🏠 Главное меню\n\n";
        $text .= "Выберите действие:";

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '📝 Создать объявление']],
                    [['text' => '⭐ Оставить отзыв']],
                    [['text' => '❓ Помощь']],
                    [['text' => '📋 Мои объявления']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }
}

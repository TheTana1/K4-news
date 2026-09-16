<?php

namespace App\Telegram\Handlers;

use App\Services\UserRegistrationService;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Models\User;
use WeStacks\TeleBot\Objects\Message;

class StartHandler
{
    public function __construct()
    {
    }

    public function handle($chatId):Message|bool
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


        $text = "👋 Привет!\n\n";
        $text .= "Я бот для публикации объявлений и отзывов.\n\n";
        $text .= "📌 Доступные команды:\n";
        $text .= "/new_ad - Создать объявление\n";
        $text .= "/new_news - Создать новость\n";
        $text .= "/help - Помощь\n";

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '📝 Новое объявление']],
                    [['text' => '📝 Новая новость']],
                    [['text' => '❓ Помощь']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }
}

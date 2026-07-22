<?php
// app/Telegram/Handlers/ReviewHandler.php

namespace App\Telegram\Handlers;

use App\Services\UserRegistrationService;
use App\Services\ReviewParserService;
use WeStacks\TeleBot\Laravel\TeleBot;
use Illuminate\Support\Facades\Log;

class ReviewHandler
{
    public function __construct(
        readonly UserRegistrationService $userRegistrationService,
        readonly ReviewParserService $reviewParserService
    ) {
    }

    /**
     * Обработка входящего сообщения с отзывом
     */
    public function handle($update): ?array
    {
        $message = $update->message ?? null;
        if (!$message) {
            return null;
        }

        $chatId = $message->chat->id ?? null;
        if (!$chatId) {
            return null;
        }

        $telegramUser = $message->from ?? null;
        if (!$telegramUser) {
            Log::error('Telegram user not found');
            return null;
        }

        // Регистрируем пользователя
        $userDb = $this->userRegistrationService->registerFromTelegram($telegramUser);

        if (!$userDb) {
            Log::error('Failed to register user', ['telegram_id' => $telegramUser->id]);
            return null;
        }

        // Парсим и сохраняем отзыв
        $review = $this->reviewParserService->parseAndSave($message, $telegramUser);
        if ($review) {
            $stars = str_repeat('★', $review->rating);

            TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Отзыв сохранён!\n\n" .
                    "⭐ Рейтинг: {$stars} ({$review->rating}/5)\n" .
                    "🆔 ID: {$review->id}\n" .
                    "📅 Дата: " . now()->format('d.m.Y H:i'),
            ]);

            return [
                'success' => true,
                'review_id' => $review->id,
                'rating' => $review->rating
            ];
        }

        Log::info('⭐ Stars not found in message', [
            'chat_id' => $chatId,
            'text_preview' => mb_substr($message->text ?? '', 0, 100)
        ]);

        return null;
    }
}

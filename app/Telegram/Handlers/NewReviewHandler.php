<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramBotService;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Models\Review;
use App\Models\User;

class NewReviewHandler
{
    public function __construct(private readonly TelegramBotService $telegramBotService)
    {
    }

    public function handle($update)
    {
        $chatId = $update->message->chat->id ?? null;
        if (!$chatId) return;

        $telegramUser = $update->message->from ?? null;
        if ($telegramUser && isset($telegramUser->id)) {
            $user = User::where('telegram_id', $telegramUser->id)->first();
            if (!$user) {
                $this->telegramBotService->registerUser($update);
            }
        }

        session(["review_{$chatId}" => ['step' => 1]]);

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "⭐ Напишите ваш отзыв.\n\n" .
                "Оцените работу сервиса, поделитесь впечатлениями.",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '🔙 Назад в меню']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    public function handleMessage($update, $message)
    {
        $chatId = $update->chat->id ?? null;
        if (!$chatId) return;

        $text = $message->text ?? '';

        if ($text === '❌ Отмена' || $text === '/cancel') {
            session()->forget("review_{$chatId}");
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Создание отзыва отменено.',
                'reply_markup' => ['remove_keyboard' => true],
            ]);
        }

        if (empty($text)) {
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Текст отзыва не может быть пустым. Попробуйте снова.',
            ]);
        }

        try {
            $telegramUser = $update->user ?? null;
            $user = null;

            if ($telegramUser && isset($telegramUser->id)) {
                $user = User::firstOrCreate(
                    ['telegram_id' => $telegramUser->id],
                    [
                        'name' => trim(($telegramUser->first_name ?? '') . ' ' . ($telegramUser->last_name ?? '')),
                        'telegram_username' => $telegramUser->username ?? null,
                        'is_active_in_group' => true,
                    ]
                );
            }

            $review = Review::create([
                'content' => $text,
                'rating' => 5,
                'telegram_chat_id' => $chatId,
                'telegram_author_id' => $user->telegram_id ?? null,
                'telegram_author_name' => $user->name ?? null,
                'user_id' => $user->id ?? null,
                'status' => 'active',
                'published_at' => now(),
            ]);

            session()->forget("review_{$chatId}");

            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "⭐ Отзыв успешно сохранён!\n\n" .
                    "Ваш отзыв:\n{$review->content}",
                'reply_markup' => ['remove_keyboard' => true],
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка сохранения отзыва: ' . $e->getMessage());
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Произошла ошибка. Попробуйте позже.',
                'reply_markup' => ['remove_keyboard' => true],
            ]);
        }
    }
}

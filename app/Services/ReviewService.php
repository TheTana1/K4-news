<?php

namespace App\Services;

use App\Models\User;

class ReviewService
{
    public function __construct(readonly TelegramService $telegramService)
    {
    }

    public function parseRating(string $text): ?int
    {
        $count = mb_substr_count($text, '★', 'UTF-8');

        if ($count === 0) {
            return null;
        }
        return min($count, 5);
    }

    public function sendMessage(string $count): bool
    {
        $stars = str_repeat('★', $count);
        $data = local_date(now());
        $text =
            "✅ Отзыв сохранён!\n\n" .
            "⭐ Рейтинг: {$stars} ({$count}/5)\n" .
            "📅 Дата: " . $data;

        $users = User::whereNotNull('telegram_id')->get();
//        dd($this->telegramService->sendMessage(
//            "429773823", $text));
        foreach ($users as $user) {
            $this->telegramService->sendMessage($user->telegram_id, $text);

        }
        return true;
    }
}

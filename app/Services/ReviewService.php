<?php

namespace App\Services;

use App\Jobs\SendReviewTelegram;
use Illuminate\Support\Facades\Log;

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
        SendReviewTelegram::dispatch((int) $count);

        Log::info('Рассылка отзыва поставлена в очередь', [
            'count' => $count,
        ]);

        return true;
    }
}

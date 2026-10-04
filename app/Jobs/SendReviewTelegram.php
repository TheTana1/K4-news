<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReviewTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public int $count
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        $users = User::whereNotNull('telegram_id')->get();

        if ($users->isEmpty()) {
            Log::info('Рассылка отзыва прервана: нет получателей', [
                'count' => $this->count,
            ]);
            return;
        }

        $stars = str_repeat('★', $this->count);

        $text = "✅ Отзыв сохранён!\n\n" .
            "⭐ Рейтинг: {$stars} ({$this->count}/5)\n" .
            "📅 Дата: " . local_date(now());

        $sent = 0;

        foreach ($users as $user) {
            $chatId = (string) $user->telegram_id;

            if ($telegramService->sendMessage($chatId, $text)) {
                $sent++;
            } else {
                Log::warning('Рассылка: не удалось отправить', [
                    'count' => $this->count,
                    'user_id' => $user->id,
                ]);
            }

            // Лимит Telegram: 30 сообщений/сек. 50 мс = 20/сек — безопасно.
            usleep(50000);
        }

        Log::info('Рассылка отзыва завершена', [
            'count' => $this->count,
            'total' => $users->count(),
            'sent' => $sent,
        ]);
    }
}

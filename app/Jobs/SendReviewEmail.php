<?php

namespace App\Jobs;

use App\Mail\ReviewNotification;
use App\Services\TelegramService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public int $count,
    ) {}

    public function handle(): void
    {
        $users = User::whereNotNull('email')
            ->whereNotNull('email_verified_at')       // только подтверждённые
            ->where('email_notifications', true)       // только подписанные
            ->get();

        if ($users->isEmpty()) {
            Log::info('Email-рассылка отзыва прервана: нет получателей');
            return;
        }

        $stars = str_repeat('★', $this->count);

        $text = "✅ Отзыв сохранён!\n\n" .
            "⭐ Рейтинг: {$stars} ({$this->count}/5)\n" .
            "📅 Дата: " . local_date(now());

        $sent = 0;

        foreach ($users as $user) {
            try{
                Mail::to($user->email)
                    ->send(new ReviewNotification(
                        $text
                    ));
                $sent++;
            }catch (\Exception $exception){
                Log::error('Email-рассылка: не удалось отправить', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $exception->getMessage()]);
            }
            usleep(100000);
        }
        Log::info('Email-рассылка отзыва завершена', [
            'total' => $users->count(),
            'sent' => $sent,
        ]);
    }
}

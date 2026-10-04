<?php

namespace App\Jobs;

use App\Mail\NewsNotification;
use App\Models\News;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public News $news,
        public string $header = '❗ Новая новость',
    ) {}

    public function handle(): void
    {
        $roleId = $this->news->role_id;

        $users = User::whereNotNull('email')
            ->whereNotNull('email_verified_at')       // только подтверждённые
            ->where('email_notifications', true)       // только подписанные
            ->when($roleId !== null, fn ($q) => $q->where('role_id', $roleId))
            ->get();

        if ($users->isEmpty()) {
            Log::info('Email-рассылка прервана: нет получателей', [
                'news_id' => $this->news->id,
            ]);
            return;
        }

        $sent = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)
                    ->send(new NewsNotification(
                        $this->news,
                        $this->header
                    ));

                $sent++;
            } catch (\Exception $e) {
                Log::error('Email-рассылка: не удалось отправить', [
                    'news_id' => $this->news->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            usleep(100000); // 100 мс = 10 писем/сек
        }

        Log::info('Email-рассылка завершена', [
            'news_id' => $this->news->id,
            'total' => $users->count(),
            'sent' => $sent,
        ]);
    }
}

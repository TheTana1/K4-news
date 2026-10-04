<?php

namespace App\Jobs;

use App\Mail\UserNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendUserEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public User $objUser,
        public string $event = 'created',
    ) {}

    public function handle(): void
    {
        $users = User::whereNotNull('email')
            ->whereNotNull('email_verified_at')
            ->where('email_notifications', true)
            ->where('id', '!=', $this->objUser->id)   // исключить самого себя
            ->get();

        if ($users->isEmpty()) {
            Log::info('Email-рассылка прервана: нет получателей', [
                'objUser' => $this->objUser->id,
            ]);
            return;
        }

        $text = $this->event === 'created'
            ? $this->buildCreatedText()
            : $this->buildUpdatedText();

        $sent = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)
                    ->send(new UserNotification(
                        text: $text,
                        header: $this->event === 'created' ? 'Новый сотрудник' : 'Изменение сотрудника',
                    ));

                $sent++;
            } catch (\Exception $e) {
                Log::error('Email-рассылка: не удалось отправить', [
                    'objUser' => $this->objUser->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            usleep(100000);
        }

        Log::info('Email-рассылка завершена', [
            'objUser' => $this->objUser->id,
            'event' => $this->event,
            'total' => $users->count(),
            'sent' => $sent,
        ]);
    }

    private function buildCreatedText(): string
    {
        $roleLabel = match ((int) $this->objUser->role_id) {
            1 => 'Новое начальство',
            2 => 'Новый менеджер',
            3 => 'Новый сотрудник кухни',
            4 => 'Новый сотрудник зала',
            5 => 'Новый сотрудник бара',
            6 => 'Новый сотрудник клининга',
            7 => 'Новый сотрудник техслужбы',
            default => 'Новый сотрудник',
        };

        return "{$roleLabel}: {$this->objUser->name}\n\n" .
            "📅 Дата: " . local_date(now());
    }

    private function buildUpdatedText(): string
    {
        $roleLabel = match ((int) $this->objUser->role_id) {
            1 => 'теперь начальство',
            2 => 'теперь менеджер',
            3 => 'теперь сотрудник кухни',
            4 => 'теперь сотрудник зала',
            5 => 'теперь сотрудник бара',
            6 => 'теперь сотрудник клининга',
            7 => 'теперь сотрудник техслужбы',
            default => 'теперь сотрудник',
        };

        return "{$this->objUser->name} {$roleLabel}!\n\n" .
            "📅 Дата: " . local_date(now());
    }
}

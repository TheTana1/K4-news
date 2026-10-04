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

class SendUserTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    /**
     * @param User $objUser Пользователь, о котором уведомляем
     * @param string $event 'created' или 'updated'
     */
    public function __construct(
        public User $objUser,
        public string $event = 'created',
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        $users = User::whereNotNull('telegram_id')->get();

        if ($users->isEmpty()) {
            Log::info('Рассылка прервана: нет пользователей с telegram_id', [
                'objUser' => $this->objUser->id,
            ]);
            return;
        }

        $text = $this->event === 'created'
            ? $this->buildCreatedText()
            : $this->buildUpdatedText();

        $sent = 0;

        foreach ($users as $user) {
            $chatId = (string) $user->telegram_id;

            if ($telegramService->sendHtmlWithRemoveKeyboard($chatId, $text)) {
                $sent++;
            } else {
                Log::warning('Рассылка: не удалось отправить', [
                    'objUser' => $this->objUser->id,
                    'user_id' => $user->id,
                ]);
            }

            usleep(50000);
        }

        Log::info('Рассылка завершена', [
            'objUser' => $this->objUser->id,
            'event' => $this->event,
            'total' => $users->count(),
            'sent' => $sent,
        ]);
    }

    private function buildCreatedText(): string
    {
        $header = match ((int) $this->objUser->role_id) {
            1 => '👨🏻‍⚖️ <b>Новое начальство</b> ',
            2 => '👨🏻‍💼 <b>Новый менеджер</b> ',
            3 => '👨🏻‍🍳 <b>Новый сотрудник кухни</b> ',
            4 => '🤵🏻 <b>Новый сотрудник зала</b> ',
            5 => '🍸 <b>Новый сотрудник бара</b> ',
            6 => '🧽 <b>Новый сотрудник клининга</b> ',
            7 => '⚙ <b>Новый сотрудник техслужбы</b> ',
            default => '👤 <b>Новый сотрудник</b> ',
        };

        return $header . e($this->objUser->name) . "!\n\n" .
            "👥 Отправлено: всем\n" .
            "📅 Дата: " . local_date(now()) . "\n";
    }

    private function buildUpdatedText(): string
    {
        $name = e($this->objUser->name);

        return match ((int) $this->objUser->role_id) {
                1 => "👨🏻‍⚖️ <b>{$name} теперь начальство!</b>\n\n",
                2 => "👨🏻‍💼 <b>{$name} теперь менеджер!</b>\n\n",
                3 => "👨🏻‍🍳 <b>{$name} теперь сотрудник кухни!</b>\n\n",
                4 => "🤵🏻 <b>{$name} теперь сотрудник зала!</b>\n\n",
                5 => "🍸 <b>{$name} теперь сотрудник бара!</b>\n\n",
                6 => "🧽 <b>{$name} теперь сотрудник клининга!</b>\n\n",
                7 => "⚙ <b>{$name} теперь сотрудник техслужбы!</b>\n\n",
                default => "👤 <b>{$name} теперь сотрудник!</b>\n\n",
            } .
            "👥 Отправлено: всем\n" .
            "📅 Дата: " . local_date(now()) . "\n";
    }
}

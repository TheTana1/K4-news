<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

readonly class UserService
{
    public function __construct(public TelegramService $telegramService)
    {
    }

    public final function sendCreateUserMessage(User $objUser): bool
    {
        $date = local_date(now());

        $users = User::whereNotNull('telegram_id')->get();

        if ($users->isEmpty()) {
            Log::info('Рассылка прервана: нет пользователей с telegram_id', [
                'objUser' => $objUser->id,
            ]);
            return false;
        }

        $header = match ((int) $objUser->role_id) {
            1 => '👨🏻‍⚖️ <b>Новое начальство</b> ',
            2 => '👨🏻‍💼 <b>Новый менеджер</b> ',
            3 => '👨🏻‍🍳 <b>Новый сотрудник кухни</b> ',
            4 => '🤵🏻 <b>Новый сотрудник зала</b> ',
            5 => '🍸 <b>Новый сотрудник бара</b> ',
            6 => '🧽 <b>Новый сотрудник клининга</b> ',
            7 => '⚙ <b>Новый сотрудник техслужбы</b> ',
            default => '👤 <b>Новый сотрудник</b> ',
        };

        $text = $header . e($objUser->name) . "!\n\n" .
            "👥 Отправлено: всем\n" .
            "📅 Дата: " . $date . "\n";

        $sent = 0;
        foreach ($users as $user) {
            if ($this->telegramService->sendHtmlWithRemoveKeyboard((string) $user->telegram_id, $text)) {
                $sent++;
            } else {
                Log::warning('Рассылка: не удалось отправить', [
                    'objUser' => $objUser->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        return $sent > 0;
    }

    public final function sendUpdateUserMessage(User $objUser): bool
    {
        $date = local_date(now());

        $users = User::whereNotNull('telegram_id')->get();

        if ($users->isEmpty()) {
            Log::info('Рассылка прервана: нет пользователей с telegram_id', [
                'objUser' => $objUser->id,
            ]);
            return false;
        }

        $text = match ((int) $objUser->role_id) {
            1 => '👨🏻‍⚖️ <b>' . e($objUser->name) . " теперь начальство!</b>\n\n",
            2 => '👨🏻‍💼 <b>' . e($objUser->name) . " теперь менеджер!</b>\n\n",
            3 => '👨🏻‍🍳 <b>' . e($objUser->name) . " теперь сотрудник кухни!</b>\n\n",
            4 => '🤵🏻 <b>' . e($objUser->name) . " теперь сотрудник зала!</b>\n\n",
            5 => '🍸 <b>' . e($objUser->name) . " теперь сотрудник бара!</b>\n\n",
            6 => '🧽 <b>' . e($objUser->name) . " теперь сотрудник клининга!</b>\n\n",
            7 => '⚙ <b>' . e($objUser->name) . " теперь сотрудник техслужбы!</b>\n\n",
            default => '👤 <b>' . e($objUser->name) . " теперь сотрудник!</b>\n\n",
        };

        $text .=
            "👥 Отправлено: всем\n" .
            "📅 Дата: " . $date . "\n";

        $sent = 0;
        foreach ($users as $user) {
            if ($this->telegramService->sendHtmlWithRemoveKeyboard((string) $user->telegram_id, $text)) {
                $sent++;
            } else {
                Log::warning('Рассылка: не удалось отправить', [
                    'objUser' => $objUser->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        return $sent > 0;
    }
}

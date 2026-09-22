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
            Log::info('Рассылка новости прервана: нет пользователей с telegram_id', [
                'objUser' => $objUser->id,
            ]);
            return false;
        }
        $header = match ((int)$objUser->role_id)
        {
            1 => '👨🏻‍⚖️ <b>Новое начальство ',
            2 => '👨🏻‍💼 <b>Новый менеджер ',
            3 => '👨‍🍳 <b>Новый сотрудник кухни ',
            4 => '🤵 <b>Новый сотрудник зала ',
            default => '👤 <b>Новый сотрудник '
        };
        $text =
            $header.$objUser->name."!</b>\n\n".
            "👥 Отправлено: всем\n" .
            "📅 Дата: " . $date . "\n";
        foreach ($users as $user) {
            if (!$this->telegramService->sendHtmlWithRemoveKeyboard($user->telegram_id, $text)) {
                Log::warning('Рассылка: не удалось отправить', [
                    'objUser' => $objUser->id,
                    'user_id' => $user->id,
                ]);
            }
        }
        return true;
    }

    public final function sendUpdateUserMessage(User $objUser): bool
    {
        $date = local_date(now());

        $users = User::whereNotNull('telegram_id')->get();

        if ($users->isEmpty()) {
            Log::info('Рассылка новости прервана: нет пользователей с telegram_id', [
                'objUser' => $objUser->id,
            ]);
            return false;
        }
        $text = match ((int)$objUser->role_id)
        {
            1 => '👨🏻‍⚖️ <b>' . $objUser->name . " теперь начальство!</b>\n\n",
            2 => '👨🏻‍💼 <b>'.$objUser->name. " теперь менеджер!</b>\n\n",
            3 => '👨‍🍳 <b>'.$objUser->name. " теперь сотрудник кухни!</b>\n\n",
            4 => '🤵 <b>'.$objUser->name. " теперь сотрудник зала!</b>\n\n",
            default => '👤 <b>'.$objUser->name. " теперь сотрудник!</b>\n\n"
        };
        $text .=
            "👥 Отправлено: всем\n" .
            "📅 Дата: " . $date . "\n";
        foreach ($users as $user) {
            if (!$this->telegramService->sendHtmlWithRemoveKeyboard($user->telegram_id, $text)) {
                Log::warning('Рассылка: не удалось отправить', [
                    'objUser' => $objUser->id,
                    'user_id' => $user->id,
                ]);
            }
        }
        return true;
    }
}

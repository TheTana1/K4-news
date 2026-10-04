<?php

namespace App\Services;

use App\Jobs\SendUserEmail;
use App\Jobs\SendUserTelegram;
use App\Models\User;
use Illuminate\Support\Facades\Log;

readonly class UserService
{
    public function __construct(public TelegramService $telegramService)
    {
    }

    public final function sendCreateUserMessage(User $objUser): bool
    {
        SendUserTelegram::dispatch($objUser, 'created');
        SendUserEmail::dispatch($objUser, 'created');

        Log::info('Рассылка о создании пользователя поставлена в очередь', [
            'objUser' => $objUser->id,
        ]);

        return true;
    }

    public final function sendUpdateUserMessage(User $objUser): bool
    {
        SendUserTelegram::dispatch($objUser, 'updated');
        SendUserEmail::dispatch($objUser, 'updated');

        Log::info('Рассылка об изменении пользователя поставлена в очередь', [
            'objUser' => $objUser->id,
        ]);

        return true;
    }
}

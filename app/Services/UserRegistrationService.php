<?php
// app/Services/UserRegistrationService.php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRegistrationService
{
    public function registerFromTelegram($user): ?User
    {
        if (!$user || !isset($user->id)) {
            Log::warning('Invalid Telegram user data');
            return null;
        }
        try {
            $userDb =  User::firstOrCreate(
                ['telegram_id' => $user->id],
                [
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'email' => $user->first_name ?? ''. $user->username . '@email.com',
                    'telegram_username' => $user->username,
                    'is_active_in_group' => true,
                    'password' => Hash::make($user->username.$user->id),
                    'role_id' => 3,
                ]
            );

            Log::info('User registered/updated', [
                'telegram_id' => $userDb->id,
                'user_id' => $userDb->id,
                'was_created' => $userDb->wasRecentlyCreated
            ]);
            return $userDb;

        } catch (\Exception $e) {
            Log::error('Failed to register user', [
                'telegram_id' => $bdUser->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

}

<?php
// app/Services/UserRegistrationService.php
namespace App\Services;

use App\Models\User;

use Illuminate\Support\Facades\Auth;
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
            $userDb = User::where('telegram_id', $user->id)->first();
            if ($userDb) {
                $userDb->update([
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'telegram_username' => $user->username,
                    'is_active_in_group' => true,
                    'updated_at' => now(),
                ]);

                Log::info('Telegram user updated', [
                    'telegram_id' => $user->id,
                    'user_id' => $userDb->id,
                    'username' => $user->username
                ]);
            } else {
                $userDb = User::create([
                    'telegram_id' => $user->id,
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'email' => $user->username . uniqid() . '@email.com',
                    'telegram_username' => $user->username,
                    'is_active_in_group' => true,
                    'password' => Hash::make($user->username . $user->id . uniqid()),
                    'role_id' => 3,
                ]);

                Log::info('New Telegram user registered', [
                    'telegram_id' => $user->id,
                    'user_id' => $userDb->id,
                    'username' => $user->username
                ]);
            }

            Auth::login($userDb);

            Log::info('After Auth::login', [
                'is_logged_in' => Auth::check(),
                'user_id' => Auth::id(),
                'current_user' => Auth::user() ? Auth::user()->id : null
            ]);
            return $userDb;

        } catch (\Exception $e) {
            Log::error('Failed to register user', [
                'telegram_id' => $userDb->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

}

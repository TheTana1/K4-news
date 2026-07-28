<?php

namespace App\Services;

use App\Models\User;

use App\Telegram\Handlers\NewUserHandler;
use Illuminate\Support\Facades\DB;
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

        $userDb = User::where('telegram_id', $user->id)->first();
        if ($userDb) {
            $this->updateUser($userDb);
        }
//        else {
//            $this->createUser($user);
//        }
        return $userDb;
    }



    public function createUser($user): ?User
    {
        DB::beginTransaction();
        try {
            $userDb = User::create([
                'telegram_id' => $user['id'],
                'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'email' => $user['email'],
                'telegram_username' => $user['username'],
                'is_active_in_group' => true,
                'password' => Hash::make($user['password']),
                'role_id' => $user['role'],
            ]);
            DB::commit();
            Log::info('New Telegram user registered', [
                'telegram_id' => $user->id,
                'user_id' => $userDb->id,
                'username' => $user->username
            ]);

            return $userDb;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to register user', [
                'telegram_id' => $userDb->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function updateUser($userDb): ?User
    {
        DB::beginTransaction();
        try {


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
            return $userDb;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update user', [
                'telegram_id' => $userDb->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

}

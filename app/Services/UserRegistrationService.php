<?php

namespace App\Services;

use App\Models\User;

use App\Telegram\Handlers\NewUserHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRegistrationService
{
    public function __construct(readonly UserService $userService)
    {
    }

    public function registerFromTelegram($from): ?User
    {
        if (!$from || !isset($from->id)) {
            Log::warning('Invalid Telegram user data');
            return null;
        }

        $userDb = User::withTrashed()->where('telegram_username', $from->username)->first();
        if (!$userDb) {
            $userDb = User::withTrashed()->where('telegram_id', $from->id)->first();
            if (!$userDb) {
                return null;
            }
            $this->updateUser($userDb, $from);

        }

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
                'password' => $user['password'],
                'role_id' => $user['role'],
            ]);
            $userDb->phones()->create([
                'phone_number' => $user['phone'],
            ]);

            DB::commit();

            Cache::tags(['users-index'])->flush();
            Cache::tags(['dashboard'])->flush();



            Log::info('Успешное создание user: ', [
                'telegram_id' => $userDb->id,
                'user_id' => $userDb->id,
                'username' => $userDb->username
            ]);

            return $userDb;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка создания user: ' , [
                'telegram_id' => $userDb->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function updateUser($userDb, $from): ?User
    {
        DB::beginTransaction();

        try {
            $userDb->update([
                'telegram_username' => $from->username,
                'updated_at' => now(),
            ]);

            DB::commit();

            Cache::tags(['users'])->flush();
            Cache::tags(['dashboard'])->flush();

            Log::info('Успешное обновление user: ', [
                'telegram_id' => $from->id,
                'user_id' => $userDb->id,
                'username' => $from->username
            ]);
            return $userDb;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка обновления user: ', [
                'telegram_id' => $userDb->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

}

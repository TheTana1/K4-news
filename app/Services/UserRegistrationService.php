<?php

namespace App\Services;

use App\Models\User;

use App\Telegram\Handlers\NewUserHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

readonly class UserRegistrationService
{
    public function __construct(public UserService $userService)
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
            if (empty($user['username'])) {
                $user['username'] = $this->generateUniqueUsername($user);
            }



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
                'telegram_id' => $user['id'] ?? null,
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

            Cache::tags(['users-index'])->flush();
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
                'telegram_id' => $userDb->telegram_id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Сгенерировать уникальный username, если у пользователя нет Telegram-ника.
     */
    private function generateUniqueUsername($user): string
    {
        // 1. База: first_name + id (или случайная строка, если нет имени)
        $base = $user['first_name']
            ? Str::slug($user['first_name'], '_')
            : 'user';

        // 2. Убираем всё, кроме букв/цифр/подчёркиваний
        $base = preg_replace('/[^a-z0-9_]/', '', strtolower($base));

        // 3. Если пусто — fallback
        if (empty($base)) {
            $base = 'user';
        }

        // 4. Первый вариант: base + id
        $candidate = $base . '_' . $user['id'];

        // 5. Проверяем уникальность
        if (!User::where('telegram_username', $candidate)->exists()) {
            return $candidate;
        }

        // 6. Если занят — добавляем случайный суффикс
        do {
            $candidate = $base . '_' . $user['id'] . '_' . Str::random(4);
        } while (User::where('telegram_username', $candidate)->exists());

        return $candidate;
    }

}

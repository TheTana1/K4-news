<?php

namespace App\Repositories;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserRepository
{
    /**
     * Создание нового пользователя
     */
    final public function store(UserRequest $request): User
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            if ($request->hasFile('avatar_path')) {
                $path = $request->file('avatar_path')->store('avatars', 'public');
                $validatedData['avatar_path'] = 'storage/' . $path;
            }
            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }
            $validatedData['likes'] = $validatedData['likes'] ?? 0;

            // Создаем пользователя
            $user = User::query()->create($validatedData);

            // Сохраняем телефоны, если есть
            if ($request->has('phones') && is_array($request->phones)) {
                foreach ($request->phones as $phoneData) {
                    if (!empty($phoneData['number'])) {
                        $user->phones()->create([
                            'phone_number' => $phoneData['number'],
                            'is_primary' => $phoneData['is_primary'] ?? false,
                        ]);
                    }
                }
            }

            DB::commit();

            Log::info('User created successfully', ['user_id' => $user->id, 'email' => $user->email]);

            return $user->load('phones', 'role');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to create user: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при создании пользователя: ' . $exception->getMessage());
        }
    }

    /**
     * Обновление пользователя
     */
    final public function update(UserRequest $request, User $user): User
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            // Обработка аватара
            if ($request->hasFile('avatar')) { // Исправлено: avatar вместо avatar_path
                // Удаляем старый аватар, если есть
                if ($user->avatar_path && file_exists(public_path($user->avatar_path))) {
                    unlink(public_path($user->avatar_path));
                }

                $path = $request->file('avatar')->store('avatars', 'public'); // Исправлено: avatar вместо avatar_path
                $validatedData['avatar_path'] = 'storage/' . $path;
            }
            unset($validatedData['avatar']); // Удаляем avatar из данных, чтобы не было конфликта

            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($validatedData['password']); // Не обновляем пароль, если он пустой
            }

            // Удаляем пустые значения, чтобы не обновлять их
            foreach ($validatedData as $key => $value) {
                if ($value === null || $value === '') {
                    unset($validatedData[$key]);
                }
            }

            // Обновляем пользователя
            $user->update($validatedData);

            // Обновляем телефоны, если есть
            if ($request->has('phones') && is_array($request->phones)) {
                $this->syncPhones($user, $request->phones);
            }

            DB::commit();

            Log::info('User updated successfully', ['user_id' => $user->id]);

            return $user->load('phones', 'role');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to update user: ' . $exception->getMessage(), [
                'user_id' => $user->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при обновлении пользователя: ' . $exception->getMessage());
        }
    }

    /**
     * Удаление пользователя
     */
    final public function destroy(User $user): bool
    {
        DB::beginTransaction();

        try {
            // Удаляем аватар, если есть
            if ($user->avatar_path && file_exists(public_path($user->avatar_path))) {
                unlink(public_path($user->avatar_path));
            }

            // Удаляем пользователя (телефоны удалятся каскадно)
            $result = $user->delete();

            DB::commit();

            Log::info('User deleted successfully', ['user_id' => $user->id]);

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to delete user: ' . $exception->getMessage(), [
                'user_id' => $user->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при удалении пользователя: ' . $exception->getMessage());
        }
    }

    /**
     * Синхронизация телефонов пользователя
     */
    protected function syncPhones(User $user, array $phones): void
    {
        $existingIds = [];

        foreach ($phones as $phoneData) {
            if (empty($phoneData['number'])) {
                continue;
            }

            if (!empty($phoneData['id'])) {
                // Обновляем существующий телефон
                $phone = Phone::find($phoneData['id']);
                if ($phone && $phone->user_id == $user->id) {
                    $phone->update([
                        'phone_number' => $phoneData['number'],
                        'is_primary' => $phoneData['is_primary'] ?? false,
                    ]);
                    $existingIds[] = $phone->id;
                }
            } else {
                // Создаём новый телефон
                $phone = $user->phones()->create([
                    'phone_number' => $phoneData['number'],
                    'is_primary' => $phoneData['is_primary'] ?? false,
                ]);
                $existingIds[] = $phone->id;
            }
        }

        // Удаляем телефоны, которых нет в массиве
        $user->phones()->whereNotIn('id', $existingIds)->delete();
    }

    /**
     * Получить пользователя с отношениями
     */
    final public function findWithRelations(int $id): ?User
    {
        return User::with(['phones', 'role'])->find($id);
    }

    /**
     * Получить всех пользователей с пагинацией
     */
    final public function getAllPaginated(int $perPage = 15)
    {
        return User::with('role')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Поиск пользователей
     */
    final public function search(string $query, int $perPage = 15)
    {
        return User::with('role')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('telegram_username', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

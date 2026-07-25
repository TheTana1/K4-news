<?php

namespace App\Repositories;

use App\Filters\UserFilter;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserRepository
{
    private const USER_PER_PAGE = 10;
    private const COMMENTS_PER_PAGE = 10;
    public function __construct(readonly UserFilter $userFilter)
    {
    }

    final public function paginate(Request $request, int $countPaginate=self::USER_PER_PAGE)
    {
        $query = User::query()->with('role');
        return $this->userFilter->apply($request,$query)->paginate($countPaginate)->withQueryString();
    }
    final public function getUser(User $user,int $countPaginate = self::COMMENTS_PER_PAGE)
    {
        $user->load(['role', 'phones']);
        return $user->comments()
            ->with(['commentable'])
            ->latest()
            ->paginate($countPaginate)
            ->withQueryString();
    }
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
            if ($request->has('phones')) {
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

            Log::info('Пользователь успешно создан: ', ['user_id' => $user->id, 'email' => $user->email]);

            return $user->load('phones', 'role');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при создании пользователя: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при создании пользователя: ' . $exception->getMessage());
        }
    }

    final public function update(UserRequest $request, User $user): User
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();;
            if ($request->hasFile('avatar')) {
                if ($user->avatar_path) {
                    File::delete(public_path($user->avatar_path));
                }
                $path = '/storage/' . $request->file('avatar')->store('avatars', 'public');
                $user->avatar_path = $path;
                unset($validatedData['avatar']);
            }
            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }
            $user->update($validatedData);
            if ($request->has('phones') && is_array($request->phones)) {
                $user->phones()->delete();
                $phones = $request->phones;
                foreach ($phones as $phoneData) {
                    if (empty($phoneData['number'])) {
                        continue;
                    }
                    $user->phones()->updateOrCreate([
                        'phone_number' => $phoneData['number'],
                        'is_primary' => $phoneData['is_primary'] ?? false,
                    ]);
                }
            }
            DB::commit();
            Log::info('Пользователь успешно обновлён: ', ['user_id' => $user->id]);

            return $user->load(['role', 'phones']);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при обновлении пользователя: ' . $exception->getMessage(), [
                'user_id' => $user->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при обновлении пользователя: ' . $exception->getMessage());
        }
    }

    final public function destroy(User $user): bool
    {
        DB::beginTransaction();

        try {
            if ($user->avatar_path && file_exists(public_path($user->avatar_path))) {
                unlink(public_path($user->avatar_path));
            }

            // Удаляем пользователя (телефоны удалятся каскадно)
            $result = $user->delete();

            DB::commit();

            Log::info('Пользователь успешно удалён: ', ['user_id' => $user->id]);

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при удалении пользователя: ' . $exception->getMessage(), [
                'user_id' => $user->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при удалении пользователя: ' . $exception->getMessage());
        }
    }

}

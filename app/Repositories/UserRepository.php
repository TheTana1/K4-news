<?php

namespace App\Repositories;

use App\Filters\UserFilter;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserRepository
{
    private const USER_PER_PAGE = 10;
    private const COMMENTS_PER_PAGE = 10;
    private const CACHE_TTL = 900; //15минут

    public function __construct(readonly UserFilter $userFilter)
    {
    }

    final public function index(Request $request, int $countPaginate = self::USER_PER_PAGE)
    {

        $key = 'users-index:' . md5(serialize([
                $request->query(),
                $countPaginate
            ]));

        return Cache::tags(['users-index'])->remember($key, self::CACHE_TTL, function () use ($request, $countPaginate) {
            return $this->userFilter
                ->apply($request, User::query())
                ->with(['role'])
                ->paginate($countPaginate)
                ->withQueryString();
        });
    }

    final public function show(User $user, int $countPaginate = self::COMMENTS_PER_PAGE)
    {
        $user = Cache::tags(['users'])->remember(
            'user:' . $user->id,
            self::CACHE_TTL,
            fn() => $user->load(['role', 'phones'])
        );

        $comments = Cache::tags(['users'])->remember(
            'user:' . $user->id . ':comments:page:' . request()->query('page'),
            self::CACHE_TTL,
            fn() => $user->comments()
                ->with(['commentable'])
                ->latest()
                ->paginate($countPaginate)
                ->withQueryString()
        );

        return ['user' => $user, 'comments' => $comments];

    }

    final public function store(UserRequest $request): User
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            if ($request->hasFile('avatar')) {
                $path = '/storage/' . $request->file('avatar')->store('avatars', 'public');
                $validatedData['avatar_path'] =  $path;
            }

            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $validatedData['likes'] = $validatedData['likes'] ?? 0;

            $user = User::query()->create($validatedData);

            if ($request->has('phones')) {
                foreach ($request->phones as $phoneData) {
                    if (!empty($phoneData['number'])) {
                        $user->phones()->create([
                            'phone_number' => $phoneData['number'],
                        ]);
                    }
                }
            }

            DB::commit();

            Cache::tags(['users'])->flush();
            Cache::tags(['dashboard'])->flush();

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
            $validatedData = $request->validated();

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

                foreach ($request->phones as $phoneData) {
                    if (empty($phoneData['number'])) {
                        continue;
                    }

                    $user->phones()->updateOrCreate([
                        'phone_number' => $phoneData['number'],
                    ]);
                }
            }

            DB::commit();

            Cache::tags(['users'])->flush();
            Cache::tags(['dashboard'])->flush();

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

            $resultPhone = $user->phones()->delete();
            $resultUser = $user->delete();
            $result = false;
            if ($resultUser && $resultPhone) $result = true;

            DB::commit();

            Cache::tags(['users'])->flush();
            Cache::tags(['dashboard'])->flush();

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

    public function edit(User $user)
    {
        return Cache::tags(['users'])->remember(
            'user:' . $user->id,
            self::CACHE_TTL,
            fn() => $user->load(['role', 'phones'])
        );
    }
}

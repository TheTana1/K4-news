<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $user = User::create($data);
            $user->phones()->create([
                'phone_number' => $data['phone'],
            ]);


            DB::commit();
            $token = JWTAuth::fromUser($user);

            return response()->json(compact('user', 'token'));
        } catch (\Exception $exception) {
            \Log::critical('Ошибка регистрации: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка регистрации: ' . $exception->getMessage());
        }
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 401);
        }

        if (!\Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => 'Неверный пароль',
            ], 401);
        }

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'JWT attempt failed'], 401);
        }

        return response()->json([
            'token' => $token,
            'user'  => auth('api')->user(),
        ]);
    }

    public function me():JsonResponse
    {
        return response()->json(auth('api')->user());
    }

    public function update(UserRequest $request):JsonResponse
    {
        $user = auth('api')->user();
        $validated = $request->validated();
        $user->update($validated);
        $user->phones()->update(['phone_number' => $validated['phones']]);

        return response()->json($user);
    }

    public function logout():JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Вы успешно вышли',
        ]);
    }
}

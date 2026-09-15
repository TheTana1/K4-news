<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Пользователь не найден'], 401);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Токен истёк'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Токен недействителен'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Токен отсутствует'], 401);
        }

        return $next($request);
    }
}


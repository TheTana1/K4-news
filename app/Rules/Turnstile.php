<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('Подтвердите, что вы не робот.');
            return;
        }

        $response = Http::timeout(5)
            ->asForm()
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => config('services.turnstile.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);


        // Обязательно проверяем HTTP-статус И поле success
        if (!$response->successful() || $response->json('success') !== true) {
            $fail('Капча не пройдена. Попробуйте снова.');
        }
    }
}

<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Auth\Events\Validated;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
//        $this->dd($user);
        return $user->isAdmin()||$user->isModerator();
    }

    public function rules(): array
    {
        $minDate = Carbon::today()->subYears(95)->format('Y-m-d');
        $maxDate = Carbon::today()->subYears(15)->format('Y-m-d');
        switch ($this->method()) {

            case 'POST': return[
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:2|confirmed',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'birthday' => 'nullable|date|before:'.$maxDate.'|after:'.$minDate,
                'gender' => 'nullable|in:0,1',
                'role_id' => 'nullable|exists:roles,id',
                'telegram_id' => 'nullable|string|max:255|unique:users,telegram_id',
                'telegram_username' => 'nullable|string|max:255',
                'is_active_in_group' => 'nullable|boolean',
                'likes' => 'nullable|integer|min:0',
                'phones' => 'nullable|array',
                'phones.*.number' => 'required_with:phones|string|max:20',
                'phones.*.is_primary' => 'nullable|boolean',];

            case 'PUT': return['name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                ],
                'password' => 'nullable|string|min:8|confirmed',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'birthday' => 'nullable|date|before:today|after:1960-01-01',
                'gender' => 'nullable|in:0,1',
                'role_id' => 'nullable|exists:roles,id',
                'telegram_id' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'telegram_username' => 'nullable|string|max:255',
                'is_active_in_group' => 'nullable|boolean',
                'likes' => 'nullable|integer|min:0',
                'phones' => 'nullable|array',
                'phones.*.id' => 'nullable|exists:phones,id',
                'phones.*.number' => 'required_with:phones|string|max:20',
                'phones.*.is_primary' => 'nullable|boolean',];
        };
        return [];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно для заполнения',
            'name.string' => 'Имя должно быть строкой',
            'name.max' => 'Имя не должно превышать 255 символов',

            'email.required' => 'Email обязателен для заполнения',
            'email.email' => 'Введите корректный email',
            'email.unique' => 'Пользователь с таким email уже существует',
            'email.max' => 'Email не должен превышать 255 символов',

            'password.required' => 'Пароль обязателен для заполнения',
            'password.min' => 'Пароль должен быть не менее 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'password.string' => 'Пароль должен быть строкой',

            'birthday.date' => 'Дата рождения должна быть корректной датой',
            'birthday.before' => 'Возраст должен быть не более 95 лет',
            'birthday.after' => 'Возраст должен быть не менее 15 лет',

            'gender.in' => 'Выберите корректный пол',

            'role_id.exists' => 'Выбранная роль не существует',

            'telegram_id.unique' => 'Пользователь с таким Telegram ID уже существует',
            'telegram_id.max' => 'Telegram ID не должен превышать 255 символов',

            'telegram_username.max' => 'Telegram username не должен превышать 255 символов',

            'is_active_in_group.boolean' => 'Неверное значение для статуса в группе',

            'likes.integer' => 'Количество лайков должно быть числом',
            'likes.min' => 'Количество лайков не может быть отрицательным',

            'avatar.image' => 'Загрузите изображение',
            'avatar.mimes' => 'Допустимые форматы: JPEG, PNG, JPG, GIF',
            'avatar.max' => 'Размер изображения не должен превышать 2MB',

            'phones.array' => 'Неверный формат телефонов',
            'phones.*.number.required_with' => 'Номер телефона обязателен',
            'phones.*.number.string' => 'Номер телефона должен быть строкой',
            'phones.*.number.max' => 'Номер телефона не должен превышать 20 символов',
            'phones.*.id.exists' => 'Телефон не найден в базе данных',
            'phones.*.is_primary.boolean' => 'Неверное значение для основного телефона',
        ];
    }
public function after()
{
    return [
        function ($validator) {
            // Проверяем, есть ли ошибки валидации
            if ($validator->errors()->any()) {
                // Валидация НЕ прошла
                Log::warning('Валидация не прошла', [
                    'errors' => $validator->errors()->toArray()
                ]);

                // Можно добавить свои ошибки
                if ($this->input('telegram_username') && !$this->input('telegram_id')) {
                    $validator->errors()->add('telegram_id', 'Если указан Telegram username, то Telegram ID обязателен');
                }

                // Можем проверить что-то еще
                if ($this->input('gender') === '1' && $this->input('name') === 'Алексей') {
                    $validator->errors()->add('name', 'Имя Алексей не может быть женского пола');
                }
            } else {
                // Валидация прошла успешно
                Log::info('Валидация прошла успешно');
            }
        }
    ];
}

    protected function prepareForValidation(): void
    {

        $password = $this->input('password');
        if ($password === 'password'|| Hash::check($password, $this->user()->password) || empty($password)) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }

    }
}

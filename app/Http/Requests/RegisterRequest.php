<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minDate = Carbon::today()->subYears(95)->format('Y-m-d');
        $maxDate = Carbon::today()->subYears(15)->format('Y-m-d');
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:2|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'birthday' => 'nullable|date|before:' . $maxDate . '|after:' . $minDate,
            'gender' => 'nullable|in:0,1',
            'role_id' => 'nullable|exists:roles,id',
            'telegram_id' => 'nullable|string|max:255|unique:users,telegram_id',
            'telegram_username' => 'required|string|max:255',
            'is_active_in_group' => 'nullable|boolean',
            'phone'              => 'required|string|max:20',
        ];
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
            'birthday.after' => 'Возраст должен быть не более 95 лет',
            'birthday.before' => 'Возраст должен быть не менее 15 лет',

            'gender.in' => 'Выберите корректный пол',

            'role_id.exists' => 'Выбранная роль не существует',

            'telegram_id.unique' => 'Пользователь с таким Telegram ID уже существует',
            'telegram_id.max' => 'Telegram ID не должен превышать 255 символов',

            'telegram_username.required' => 'Telegram username обязателен',
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
        ];
    }
}

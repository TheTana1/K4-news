<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $userId = $this->route('user')?->id;

        return match ($this->method()) {
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules($userId),
            default => [],
        };
    }

    protected function storeRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|in:0,1',
            'role_id' => 'nullable|exists:roles,id',
            'telegram_id' => 'nullable|string|max:255|unique:users,telegram_id',
            'telegram_username' => 'nullable|string|max:255',
            'is_active_in_group' => 'nullable|boolean',
            'likes' => 'nullable|integer|min:0',
            'phones' => 'nullable|array',
            'phones.*.number' => 'required_with:phones|string|max:20',
            'phones.*.is_primary' => 'nullable|boolean',
        ];
    }

    protected function updateRules($userId): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|in:0,1',
            'role_id' => 'nullable|exists:roles,id',
            'telegram_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'telegram_username' => 'nullable|string|max:255',
            'is_active_in_group' => 'nullable|boolean',
            'likes' => 'nullable|integer|min:0',
            'phones' => 'nullable|array',
            'phones.*.id' => 'nullable|exists:phones,id',
            'phones.*.number' => 'required_with:phones|string|max:20',
            'phones.*.is_primary' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно для заполнения',
            'email.required' => 'Email обязателен для заполнения',
            'email.email' => 'Введите корректный email',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.required' => 'Пароль обязателен для заполнения',
            'password.min' => 'Пароль должен быть не менее 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active_in_group')) {
            $this->merge([
                'is_active_in_group' => filter_var($this->is_active_in_group, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        if ($this->has('gender') && $this->gender !== null && $this->gender !== '') {
            $this->merge([
                'gender' => (int) $this->gender
            ]);
        }
    }
}

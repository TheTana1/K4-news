<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     */
    public function authorize(): bool
    {
        return true; // Разрешаем всем авторизованным, можно добавить проверку прав
    }

    /**
     * Правила валидации в зависимости от метода запроса
     */
    public function rules(): array
    {
        switch ($this->method()) {
            case 'POST':          return [
                'content' => 'required|string|min:10|max:10000',
                'status' => 'nullable|in:active,inactive',
            ];

            case 'PUT': return [
                'content' => 'sometimes|string|min:10|max:10000',
                'status' => 'nullable|in:active,inactive',
            ];
        };
        return [];
    }

    public function messages(): array
    {
        return [

            // Содержание
            'content.required' => 'Содержание новости обязательно',
            'content.min' => 'Содержание должно содержать минимум :min символов',
            'content.max' => 'Содержание не может быть длиннее :max символов',

            // Изображение
            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Допустимые форматы: jpeg, png, jpg, gif, webp',
            'image.max' => 'Размер файла не должен превышать 2MB',

            // Статус
            'status.in' => 'Статус должен быть active или inactive',
        ];
    }



}

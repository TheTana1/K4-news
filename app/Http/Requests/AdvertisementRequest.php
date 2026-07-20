<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvertisementRequest extends FormRequest
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
            case 'POST':  return [
                'content' => 'required|string|min:10|max:10000',
                'status' => 'nullable|in:active,inactive',
                'author_id' => 'nullable|exists:users,id',
                'telegram_author_name' => 'nullable|string|max:255',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file|mimes:pdf,txt,jpg,jpeg,png,gif,bmp,webp,svg|max:10240'
            ];

            case 'PUT':  return [
                'content' => 'sometimes|string|min:10|max:10000',
                'status' => 'nullable|in:active,inactive',
                'author_id' => 'nullable|exists:users,id',
                'telegram_author_name' => 'nullable|string|max:255',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file|mimes:pdf,txt,jpg,jpeg,png,gif,bmp,webp,svg|max:10240',
                'delete_files' => 'nullable|array',
                'delete_files.*' => 'exists:files,id'
            ];
        };
        return [];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Содержание объявления обязательно',
            'content.min' => 'Содержание должно содержать минимум :min символов',
            'content.max' => 'Содержание не может быть длиннее :max символов',
            'status.in' => 'Статус должен быть active или inactive',
            'files.*.file' => 'Загруженный файл должен быть валидным',
            'files.*.max' => 'Размер файла не должен превышать :max KB',
            'files.*.mimes' => 'Разрешены только файлы форматов: :values',
            'delete_files.*.exists' => 'Выбранный файл для удаления не существует',
        ];
    }

}

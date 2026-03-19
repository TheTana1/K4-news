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
        $newsId = $this->route('news')?->id;

        return match ($this->method()) {
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules($newsId),
            default => [],
        };
    }

    /**
     * Правила для создания новости
     */
    protected function storeRules(): array
    {
        return [
            'content' => 'required|string|min:10|max:10000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    /**
     * Правила для обновления новости
     */
    protected function updateRules($newsId): array
    {
        return [
            'content' => 'sometimes|string|min:10|max:10000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    /**
     * Кастомные сообщения об ошибках
     */
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

    /**
     * Подготовка данных перед валидацией
     */
    protected function prepareForValidation(): void
    {

        // Очищаем контент от лишних пробелов
        if ($this->has('content')) {
            $this->merge([
                'content' => trim($this->content)
            ]);
        }
    }

    /**
     * Дополнительная валидация после основной
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Проверяем, что контент не состоит только из пробелов
            if ($this->has('content') && strlen(trim($this->content)) < 10) {
                $validator->errors()->add('content', 'Содержание не может состоять только из пробелов');
            }
        });
    }
}

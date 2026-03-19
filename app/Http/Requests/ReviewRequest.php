<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     */
    public function authorize(): bool
    {
        return true; // Разрешаем всем, можно добавить проверку auth()->check() если нужно
    }

    /**
     * Правила валидации в зависимости от метода запроса
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            default => [],
        };
    }

    /**
     * Правила для создания отзыва
     */
    protected function storeRules(): array
    {
        return [
            'content' => 'required|string|min:3|max:5000',
            'rating' => 'nullable|integer|min:1|max:5',
            'author_name' => [
                'nullable',
                'string',
                'max:255',
                'required',
            ],
        ];
    }

    /**
     * Правила для обновления отзыва
     */
    protected function updateRules(): array
    {
        return [
            'content' => 'sometimes|string|min:3|max:5000',
            'rating' => 'nullable|integer|min:1|max:5',
            'author_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Кастомные сообщения об ошибках
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Текст отзыва обязателен для заполнения',
            'content.min' => 'Отзыв должен содержать минимум :min символа',
            'content.max' => 'Отзыв не может быть длиннее :max символов',

            'rating.integer' => 'Оценка должна быть числом',
            'rating.min' => 'Оценка должна быть не меньше :min',
            'rating.max' => 'Оценка должна быть не больше :max',

            'author_name.required' => 'Имя обязательно для заполнения',
            'author_name.max' => 'Имя не может быть длиннее :max символов',
        ];
    }

    /**
     * Подготовка данных перед валидацией
     */
    protected function prepareForValidation(): void
    {
        // Приводим рейтинг к целому числу
        if ($this->has('rating') && $this->rating !== null) {
            $this->merge([
                'rating' => (int) $this->rating
            ]);
        }

    }


}

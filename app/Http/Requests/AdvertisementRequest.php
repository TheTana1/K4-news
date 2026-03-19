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
        $advertisementId = $this->route('advertisement')?->id;

        return match ($this->method()) {
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules($advertisementId),
            default => [],
        };
    }

    /**
     * Правила для создания объявления
     */
    protected function storeRules(): array
    {
        return [
            'content' => 'required|string|min:10|max:10000',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }

    /**
     * Правила для обновления объявления
     */
    protected function updateRules($advertisementId): array
    {
        return [
            'content' => 'sometimes|string|min:10|max:10000',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }

    /**
     * Кастомные сообщения об ошибках
     */
    public function messages(): array
    {
        return [

            // Содержание
            'content.required' => 'Содержание объявления обязательно',
            'content.min' => 'Содержание должно содержать минимум :min символов',
            'content.max' => 'Содержание не может быть длиннее :max символов',

            // Статус
            'status.in' => 'Статус должен быть active или inactive',
        ];
    }

    /**
     * Подготовка данных перед валидацией
     */
    protected function prepareForValidation(): void
    {
        // Устанавливаем статус по умолчанию, если не передан
        if (!$this->has('status')) {
            $this->merge([
                'status' => 'active'
            ]);
        }

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

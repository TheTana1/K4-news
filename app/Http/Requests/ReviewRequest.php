<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $user->isModerator();
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'content' => 'required|string|min:3|max:5000',
                    'rating' => 'nullable|integer|min:1|max:5',
                ];

            case 'PUT':
                return [
                    'content' => 'sometimes|string|min:3|max:5000',
                    'rating' => 'nullable|integer|min:1|max:5',
                ];
        };
        return [];
    }


    public function messages(): array
    {
        return [
            'content.required' => 'Текст отзыва обязателен для заполнения',
            'content.min' => 'Отзыв должен содержать минимум :min символа',
            'content.max' => 'Отзыв не может быть длиннее :max символов',
            'content.string' => 'Отзыв должен быть заполнен текстом',

            'rating.integer' => 'Оценка должна быть числом',
            'rating.min' => 'Оценка должна быть не меньше :min',
            'rating.max' => 'Оценка должна быть не больше :max',

        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('rating') && $this->rating !== null) {
            $this->merge([
                'rating' => (int)$this->rating
            ]);
        }

    }


}

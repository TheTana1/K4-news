<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class NewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->isAdmin()||$user->isModerator();
    }
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


            'content.required' => 'Содержание новости обязательно',
            'content.min' => 'Содержание должно содержать минимум :min символов',
            'content.max' => 'Содержание не может быть длиннее :max символов',
            'content.string' => 'Новость должна быть заполнена текстом',

            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Допустимые форматы: jpeg, png, jpg, gif, webp',
            'image.max' => 'Размер файла не должен превышать 2MB',

            'status.in' => 'Статус должен быть active или inactive',
        ];
    }

    public function after()
    {
        return [
            function ($validator) {
                if ($validator->errors()->any()) {
                    Log::warning('Валидация не прошла', [
                        'errors' => $validator->errors()->toArray()
                    ]);
                } else {
                    Log::info('Валидация прошла успешно');
                }
            }
        ];
    }

}

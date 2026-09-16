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
                'content' => 'required|string|min:4|max:10000',
                'status' => 'nullable|in:active,inactive',
                'telegram_author_name' => 'nullable|string|max:255',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,bmp,webp,svg|max:10240',
                'role_id' => '|integer|exists:roles,id',
            ];

            case 'PUT': return [
                'content' => 'sometimes|string|min:4|max:10000',
                'status' => 'nullable|in:active,inactive',
                'telegram_author_name' => 'nullable|string|max:255',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,bmp,webp,svg|max:10240',
                'delete_files' => 'nullable|array',
                'delete_files.*' => 'exists:files,id',
                'role_id' => '|integer|exists:roles,id',
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

            'files.*.file' => 'Загруженный файл должен быть валидным',
            'files.*.max' => 'Размер файла не должен превышать :max KB',
            'files.*.mimes' => 'Разрешены только файлы форматов: :values',

            'status.in' => 'Статус должен быть active или inactive',
        ];
    }

    public function after():array
    {
        return [
            function ($validator) {
                if ($validator->errors()->any()) {
                    Log::warning('Валидация не прошла', [
                        'errors' => $validator->errors()->toArray()
                    ]);
                } else {
                    Log::info('Валидация новости прошла успешно');
                }
            }
        ];
    }

}

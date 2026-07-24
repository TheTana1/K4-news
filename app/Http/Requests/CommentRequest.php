<?php

namespace App\Http\Requests;

use App\Models\Advertisement;
use App\Models\News;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    protected $modelMap = [
        'advertisement' => Advertisement::class,
        'news' => News::class,
        'review' => Review::class,
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'comment' => 'required|string|max:2000',
                    'commentable_id' => 'required|integer',
                    'commentable_type' => 'required|string|in:advertisement,news,review',
                ];

            case 'PUT':
                return [
                    'comment' => 'required|string|max:2000',
                ];
        };
        return [];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'Текст комментария обязателен.',
            'comment.max' => 'Комментарий не может быть длиннее :max символов.',
            'commentable_type.in' => 'Некорректный тип комментария.',
            'commentable_id.exists' => 'Запись не найдена.',
        ];
    }
}

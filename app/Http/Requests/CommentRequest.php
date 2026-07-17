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
        return match ($this->method()) {
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            default => [],
        };
    }

    protected function storeRules(): array
    {
        return [
            'comment' => 'required|string|max:2000',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string|in:advertisement,news,review',
        ];
    }

    protected function updateRules(): array
    {
        return [
            'comment' => 'required|string|max:2000',
        ];
    }

    public function getCommentable(): ?\Illuminate\Database\Eloquent\Model
    {
        if ($this->method() !== 'POST') {
            return null;
        }

        $type = $this->input('commentable_type');
        $id = $this->input('commentable_id');

        if (!isset($this->modelMap[$type])) {
            return null;
        }

        return $this->modelMap[$type]::find($id);
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

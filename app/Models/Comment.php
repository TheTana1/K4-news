<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'comment',
        'user_id',
        'commentable_id',
        'commentable_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
//    protected function getCommentable_typeAttribute()
//    {
//        return class_basename($this->attributes['commentable_type']);
//    }
    protected function getSourceAttribute(): string
    {
        $model = $this->commentable;
        if(!$model) return 'Удалено';
        return match (get_class($model)) {
            \App\Models\Advertisement::class => $model->content ?? 'Объявление #' . $model->id,
            \App\Models\News::class => $model->content ?? 'Новость #' . $model->id,
            \App\Models\Review::class => $model->content ?? $model->name ?? 'Отзыв #' . $model->id,
            default => 'Запись #' . $model->id,
        };
    }

    protected function setCommentable_typeAttribute() //Аллилуя
    {
        $this->attributes['commentable_type'] = get_class($this);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

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
    protected $appends = ['source'];
    /**
     * Получить родительскую модель (Advertisement, News или Review)
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
    public function getSourceAttribute(): ?string
    {
        if (!$this->commentable) {
            return 'Удалено';
        }
        return $this->commentable->content;
    }

//    public function getSourceTypeAttribute(): string
//    {
//        if (!$this->commentable) {
//            return 'Удалено';
//        }
//
//        return class_basename($this->commentable);
//    }
//
//    public function getSourceIdAttribute(): int
//    {
//        if(!$this->commentable){
//            return 'Удалено';
//        }
//        return $this->commentable->id;
//    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

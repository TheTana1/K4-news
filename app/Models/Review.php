<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Review extends Model
{
    protected $fillable = [
        'content',
        'rating',
        'telegram_message_id',
        'telegram_chat_id',
        'telegram_author_id',
        'telegram_author_name',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

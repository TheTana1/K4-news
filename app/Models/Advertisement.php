<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Advertisement extends Model
{
    protected $fillable = [
        'content',
        'image_path',
        'telegram_message_id',
        'telegram_chat_id',
        'telegram_author_id',
        'telegram_author_name',
        'views',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
        'price' => 'integer',
    ];

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'telegram_author_id', 'telegram_id');
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFromTelegram($query)
    {
        return $query->whereNotNull('telegram_message_id');
    }
}

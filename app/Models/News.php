<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class News extends Model
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
    ];

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'telegram_author_id', 'telegram_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Advertisement extends Model
{
    protected $fillable = [
        'content',
        'telegram_author_name',
        'published_at',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'disk',
        'status',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
        'price' => 'integer',
    ];
    public function files():MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

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

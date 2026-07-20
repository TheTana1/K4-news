<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class News extends Model
{
    protected $fillable = [
        'content',
        'telegram_message_id',
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
    ];

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function files():MorphMany
    {
       return $this->morphMany(File::class, 'fileable');
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

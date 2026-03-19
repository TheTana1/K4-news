<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'avatar_path',
        'name',
        'email',
        'password',
        'role_id',
        'birthday',
        'gender',
        'likes',
        'telegram_id',
        'telegram_username',
        'is_active_in_group',
        'joined_at',
        'left_at',
        'last_post_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'gender' => 'boolean',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'last_post_at' => 'datetime',
            'is_active_in_group' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'telegram_author_id', 'telegram_id');
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'telegram_author_id', 'telegram_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    // Скоупы
    public function scopeActiveInGroup($query)
    {
        return $query->where('is_active_in_group', true);
    }

    public function scopeWithTelegram($query)
    {
        return $query->whereNotNull('telegram_id');
    }

    // Хелперы для проверки ролей
    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function isModerator(): bool
    {
        return $this->role?->slug === 'moderator';
    }
}

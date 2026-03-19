<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['slug', 'label'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Хелперы для проверки ролей
    public function isAdmin(): bool
    {
        return $this->slug === 'admin';
    }

    public function isModerator(): bool
    {
        return $this->slug === 'moderator';
    }
}

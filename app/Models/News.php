<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string $content
 * @property string|null $telegram_author_name
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereTelegramAuthorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class News extends Model
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
        'role_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
    ];
    public function scopeForCurrentUser($query)
    {
        if (Auth::check()) {
            $roleId = Auth::user()->role_id;
            if($roleId===1||$roleId===2){
                return $query;
            }
            return $query->whereIn('role_id',[ $roleId ?? 2, 1,2]);
        }
        return $query->where('role_id', 2);
    }
    public function role():BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
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

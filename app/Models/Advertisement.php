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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement whereTelegramAuthorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advertisement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'role_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
        'price' => 'integer',
    ];
    public function scopeForRole($query, $roleId = null)
    {
        if ($roleId) {
            return $query->where('role_id', $roleId);
        }
        return $query;
    }

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
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function files():MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }



}

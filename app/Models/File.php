<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $fileable_type
 * @property int $fileable_id
 * @property string $file_name
 * @property string $file_path
 * @property string $file_size
 * @property string $mime_type
 * @property string $disk
 * @property string|null $description
 * @property-read Model|\Eloquent $fileable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFileableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFileableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class File extends Model
{
    protected $fillable=[
        'fileable_id',
        'fileable_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'disk',
        'description',
    ];

    public function fileable():MorphTo
    {
        return $this->morphTo();
    }

//    public function getFormattedSizeAttribute(): string
//    {
//        $bytes = $this->file_size;
//        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
//
//        $i = 0;
//        while ($bytes >= 1024 && $i < count($units) - 1) {
//            $bytes /= 1024;
//            $i++;
//        }
//
//        return round($bytes, 2) . ' ' . $units[$i];
//    }
}

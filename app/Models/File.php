<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

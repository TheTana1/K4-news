<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $phone_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PhoneFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone whereUserId($value)
 * @mixin \Eloquent
 */
class Phone extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'phone_number',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

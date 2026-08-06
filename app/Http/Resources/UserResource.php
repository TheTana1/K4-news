<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /*
    * @return array<string, mixed>
    */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'gender' => $this->gender ===1?'female':'male',
            'telegram_username' => $this->telegram_username,
            'telegram_id' => $this->telegram_id,
            'avatar_path' => $this->avatar_path,

            'role' => $this->role,
            'comments' => $this->comments


        ];
    }
}

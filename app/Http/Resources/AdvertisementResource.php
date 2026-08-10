<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'content' => $this->content,
            'telegram_author_name' => $this->telegram_author_name,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'role_id' => $this->role_id
        ];
    }
}

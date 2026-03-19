<?php

namespace App\Repositories;

use App\Http\Requests\AdvertisementRequest;
use App\Models\Advertisement;

class AdvertisementRepository
{
    final public function store(AdvertisementRequest $request): Advertisement
    {
        $validated = $request->validated();
        return Advertisement::query()->create($validated);
    }
}

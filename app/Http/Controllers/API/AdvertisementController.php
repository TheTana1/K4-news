<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvertisementRequest;
use App\Http\Resources\AdvertisementResource;
use App\Models\Advertisement;
use App\Repositories\AdvertisementRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdvertisementController extends Controller
{
    public function __construct(
        readonly AdvertisementRepository $AdvertisementRepository,
    )
    {
    }

    public function index(): AnonymousResourceCollection
    {
        return AdvertisementResource::collection($this->AdvertisementRepository->index());
    }

    public function show(Advertisement $Advertisement): AdvertisementResource
    {

        return new AdvertisementResource($Advertisement->load([
            'files',
            'role',
            'comments'
        ]));
    }

    public function store(AdvertisementRequest $request): AdvertisementResource
    {
        return new AdvertisementResource($this->AdvertisementRepository->store($request));
    }

    public function update(AdvertisementRequest $request, Advertisement $Advertisement): AdvertisementResource
    {
        return new AdvertisementResource($this->AdvertisementRepository->update($request, $Advertisement));
    }

    public function destroy(Advertisement $Advertisement): JsonResponse
    {
        return response()->json([
            'status' => $this->AdvertisementRepository->destroy($Advertisement) ? 'success' : 'failure',
        ]);
    }
}

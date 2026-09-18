<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Repositories\reviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    public function __construct(
        readonly reviewRepository $reviewRepository,
    )
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return ReviewResource::collection($this->reviewRepository->index($request));
    }

    public function show(Review $review): ReviewResource
    {

        return new ReviewResource($review->load([
            'files',
            'role',
            'comments'
        ]));
    }


    public function destroy(Review $review): JsonResponse
    {
        return response()->json([
            'status' => $this->reviewRepository->delete($review) ? 'success' : 'failure',
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use App\Repositories\NewsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NewsController extends Controller
{
    public function __construct(
        readonly NewsRepository $newsRepository,
    )
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return NewsResource::collection($this->newsRepository->paginate());
    }

    public function show(News $news): NewsResource
    {

        return new NewsResource($news->load([
            'files',
            'role',
            'comments'
        ]));
    }

    public function store(NewsRequest $request): NewsResource
    {
        return new NewsResource($this->newsRepository->store($request));
    }

    public function update(NewsRequest $request, News $news): NewsResource
    {
        return new NewsResource($this->newsRepository->update($request, $news));
    }

    public function destroy(News $news): JsonResponse
    {
        return response()->json([
            'status' => $this->newsRepository->destroy($news) ? 'success' : 'failure',
        ]);
    }
}

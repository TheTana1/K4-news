<?php

namespace App\Http\Controllers\API;

use App\Filters\UserFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class UserController extends Controller
{

    public function __construct(
        readonly UserRepository      $userRepository,
        readonly UserFilter $userFilter,
    )
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return UserResource::collection($this->userRepository->paginate($request));
    }

    public function show(User $user): UserResource
    {

        return new UserResource($user->load(['role:label',
            'comments:comment']));
    }

    public function store(UserRequest $request): UserResource
    {
        return new UserResource($this->userRepository->store($request));
    }

    public function update(UserRequest $request, User $user): UserResource
    {
        return new UserResource($this->userRepository->update($request, $user));
    }

    public function destroy(User $user): JsonResponse
    {
        return response()->json([
            'status' => $this->userRepository->destroy($user) ? 'success' : 'failure',
        ]);
    }


}

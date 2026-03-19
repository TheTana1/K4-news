<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(readonly UserRepository $userRepository)
    {

    }
    public function index():View
    {
        $users = User::query()->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create', ['roles' => Role::all()]);
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user'=>$user,'roles'=>Role::all(),
        ]);
    }

    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    public function store(UserRequest $request): RedirectResponse
    {

        return redirect()->route('users.show', $this->userRepository->store($request));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        return redirect()->route('users.show', $this->userRepository->update($request, $user));
    }

    public function destroy(User $user): RedirectResponse
    {
        return redirect()->route('users.index', $this->userRepository->destroy($user));
    }
}

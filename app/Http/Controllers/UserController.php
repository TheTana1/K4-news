<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;


class UserController extends Controller
{



    public function __construct(readonly UserRepository $userRepository)
    {

    }
    public function index():View
    {
        $users = $this->userRepository->paginate();
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = $this->getRoles();
        return view('users.create', compact('roles'));
    }

    public function edit(User $user): View
    {
        $roles = $this->getRoles();

        return view('users.edit', compact('roles', 'user'));
    }

    public function show(User $user): View
    {

        $comments =$this->userRepository->paginateUserComments($user);
        return view('users.show', compact('user','comments'));
    }

    public function store(UserRequest $request): RedirectResponse
    {

        return redirect()->route('users.show', $this->userRepository->store($request))
            ->with('success', 'Пользователь успешно создан');
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        return redirect()->route('users.show', $this->userRepository->update($request, $user))
            ->with('success', 'Пользователь успешно обновлён');
    }

    public function destroy(User $user): RedirectResponse
    {
        $result = $this->userRepository->destroy($user);
        return $result ?
            redirect()->route('users.index')->with('success','Успешное удаление пользователя'):
            redirect()->route('users.index')->with('error','Ошибка удаления пользователя');
    }

    private function getRoles()
    {
        return Role::all();
    }
}

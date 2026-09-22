<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;


class UserController extends Controller
{


    public function __construct(
        readonly UserService    $userService,
        readonly UserRepository $userRepository)
    {

        $this->authorizeResource(User::class, 'user', [
            'only' => ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'],
        ]);

    }

    public function index(Request $request): View
    {

        $users = $this->userRepository->index($request);
        $roles = $this->getRoles();

        return view('users.index', compact('users', 'roles'));
    }

    public function indexTrashed(Request $request): View
    {
        $this->authorize('viewTrashed', User::class);
        $users = $this->userRepository->indexTrashed($request);
        $roles = $this->getRoles();

        return view('users.trashed', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = $this->getRoles();
        return view('users.create', compact('roles'));
    }

    public function edit(User $user): View
    {
        $roles = $this->getRoles();
        $user = $this->userRepository->edit($user);

        return view('users.edit', compact('roles', 'user'));
    }

    public function show(User $user): View
    {
        $data = $this->userRepository->show($user);
        return view('users.show', [
            'user' => $data['user'],
            'comments' => $data['comments']
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = $this->userRepository->store($request);
        if (!$user) {
            return back()
                ->withInput()
                ->with('error', 'Не удалось создать пользователя');
        }

        $this->userService->sendCreateUserMessage($user);

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Пользователь успешно создан');
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $oldRole = $user->role_id;
        $this->userRepository->update($request, $user);
        if (!$user) {
            return back()
                ->withInput()
                ->with('error', 'Не удалось обновить пользователя');
        }
        if ($oldRole != $user->role_id) {
            $this->userService->sendUpdateUserMessage($user);
        }

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Пользователь успешно обновлён');
    }

    public function destroy(User $user): RedirectResponse
    {
        $result = $this->userRepository->destroy($user);
        return $result ?
            redirect()->route('users.index')->with('success', 'Успешное удаление пользователя') :
            redirect()->route('users.index')->with('error', 'Ошибка удаления пользователя');
    }

    private function getRoles()
    {
        return Role::all();
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $result = $this->userRepository->restore($user);
        return $result ?
            redirect()->route('trashed-users.index')->with('success', 'Пользователь восстановлен') :
            redirect()->route('trashed-users.index')->with('error', 'Ошибка восстановления пользователя');

    }

    public function forceDelete(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $result = $this->userRepository->forceDelete($user);
        return $result ?
            redirect()->route('trashed-users.index')->with('success', 'Пользователь удалён навсегда') :
            redirect()->route('trashed-users.index')->with('error', 'Ошибка полного удаления пользователя');
    }
}

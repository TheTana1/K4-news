@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Пользователи</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            + Добавить пользователя
        </a>
    </div>

    <!-- Статистика -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-people fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Всего</div>
                        <div class="h5 mb-0">{{ $users->total() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">В группе</div>
                        <div class="h5 mb-0">{{ $users->where('is_active_in_group', true)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-gender-male fs-4 text-info"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Мужчины</div>
                        <div class="h5 mb-0">{{ $users->where('gender', 0)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-gender-female fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Женщины</div>
                        <div class="h5 mb-0">{{ $users->where('gender', 1)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Telegram</th>
                        <th>Дата</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @if($user->avatar_path)
                                            <img src="{{ asset($user->avatar_path) }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                        @else
                                            @php
                                                $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                                                $color = $colors[abs(crc32($user->name)) % count($colors)];
                                            @endphp
                                            <div class="rounded-circle bg-gradient-to-r from-blue-500 to-purple-600 d-flex align-items-center justify-content-center text-white fw-bold bg-{{ $color }}" style="width:40px;height:40px;font-size:1.2rem;">
                                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        @if($user->telegram_username)
                                            <small class="text-muted">@ {{ $user->telegram_username }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleColors = [
                                        'admin' => 'bg-purple-100 text-purple-800',
                                        'moderator' => 'bg-blue-100 text-blue-800',
                                        'user' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $roleSlug = $user->role?->slug ?? 'user';
                                @endphp
                                <span class="badge {{ $roleColors[$roleSlug] ?? $roleColors['user'] }}">
                                        {{ $user->role?->label ?? 'Пользователь' }}
                                    </span>
                            </td>
                            <td>
                                @if($user->is_active_in_group)
                                    <span class="badge bg-success">В группе</span>
                                @else
                                    <span class="badge bg-secondary">Не в группе</span>
                                @endif
                            </td>
                            <td>
                                @if($user->telegram_id)
                                    <span class="text-success">Подключен</span>
                                @else
                                    <span class="text-muted">Нет</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d.m.Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary">Просмотр</a>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-success">Ред.</a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить пользователя?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Пользователей пока нет</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Корзина')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Корзина</h1>
        <div class="d-flex gap-2">

            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-trash me-1"></i> Назад
            </a>

        </div>
    </div>
    <!-- Фильтр -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('trashed-users.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Имя -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Имя</label>
                        <input type="text"
                               name="name"
                               class="form-control form-control-sm"
                               placeholder="Поиск по имени"
                               value="{{ request('name') }}">
                    </div>

                    <!-- Email -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Email</label>
                        <input type="text"
                               name="email"
                               class="form-control form-control-sm"
                               placeholder="Поиск по email"
                               value="{{ request('email') }}">
                    </div>

                    <!-- Username -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">TG Никнейм</label>
                        <input type="text"
                               name="username"
                               class="form-control form-control-sm"
                               placeholder="Поиск по никнейму"
                               value="{{ request('username') }}">
                    </div>

                    <!-- Роль -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Роль</label>
                        <select name="role_id" class="form-select form-select-sm">
                            <option value="">Все роли</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Дата от -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Дата удаления от</label>
                        <input type="date"
                               name="date_from"
                               class="form-control form-control-sm"
                               value="{{ request('date_from') }}">
                    </div>

                    <!-- Дата до -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Дата удаления до</label>
                        <input type="date"
                               name="date_to"
                               class="form-control form-control-sm"
                               value="{{ request('date_to') }}">
                    </div>

                    <!-- Кнопки -->
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search me-1"></i> Применить
                            </button>
                            <a href="{{ route('trashed-users.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Список пользователей -->
    <div class="card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($users as $user)
                    <div class="list-group-item p-3">
                        <div class="row g-3">
                            <!-- Аватар и имя -->
                            <div class="col-12 col-lg-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @if($user->avatar_path)
                                            <img src="{{ asset($user->avatar_path) }}"
                                                 alt="{{ $user->name }}"
                                                 class="rounded-circle"
                                                 width="48"
                                                 height="48"
                                                 style="object-fit: cover;">
                                        @else
                                            @php
                                                $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                                                $color = $colors[abs(crc32($user->name)) % count($colors)];
                                            @endphp
                                            <div
                                                class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-{{ $color }}"
                                                style="width:48px;height:48px;font-size:1.2rem;flex-shrink:0;">
                                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-width-0">
                                        <div class="fw-bold text-truncate"
                                             style="max-width: 150px;">{{ $user->name }}</div>
                                        @if($user->telegram_username)
                                            <small class="text-muted d-block text-truncate"
                                                   style="max-width: 150px;">{{ '@'.$user->telegram_username }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Информация -->
                            <div class="col-12 col-lg-6">
                                <div class="row g-2">
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <small class="text-muted d-block">Роль</small>
                                            @php
                                                $roleColors = [
                                                    'admin' => 'text-purple',
                                                    'moderator' => 'text-info',
                                                    'user' => 'text-secondary',
                                                ];
                                                $roleSlug = $user->role?->slug ?? 'user';
                                            @endphp
                                            <span class="{{ $roleColors[$roleSlug] ?? $roleColors['user'] }}">
                                {{ $user->role?->label ?? 'Пользователь' }}
                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <small class="text-muted d-block">Статус</small>
                                            @if($user->is_active_in_group)
                                                <span class="badge bg-success">В группе</span>
                                            @else
                                                <span class="badge bg-secondary">Не в группе</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="info-item">
                                            <small class="text-muted d-block">Регистрация</small>
                                            <span class="small">{{ $user->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Действия -->
                            @auth()

                                <div class="col-12 col-lg-3">
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="{{ route('users.show', $user) }}"
                                           class="btn btn-sm btn-outline-primary flex-fill">
                                            <i class="bi bi-eye me-1"></i> Просмотр
                                        </a>
                                        @can('restore', $user)
                                            <form action="{{ route('users.restore', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success" title="Восстановить">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        @endcan

                                        @can('forceDelete', $user)

                                            <form action="{{ route('users.forceDelete', $user->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Удалить пользователя {{ $user->name }} навсегда?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Удалить навсегда">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-people fs-1 d-block mb-3"></i>
                        <p class="mb-0">Пользователей пока нет</p>
                    </div>
                @endforelse
                @if($users->hasPages())
                    <div class="card-footer">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

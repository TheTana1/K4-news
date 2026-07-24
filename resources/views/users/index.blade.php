@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Пользователи</h1>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Добавить пользователя
        </a>
    </div>

    <!-- Статистика -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-people fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Всего</div>
                        <div class="h4 mb-0">{{ $users->total() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">В группе</div>
                        <div class="h4 mb-0">{{ $users->where('is_active_in_group', true)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-gender-male fs-4 text-info"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Мужчины</div>
                        <div class="h4 mb-0">{{ $users->where('gender', 0)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-gender-female fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Женщины</div>
                        <div class="h4 mb-0">{{ $users->where('gender', 1)->count() }}</div>
                    </div>
                </div>
            </div>
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
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-{{ $color }}"
                                                 style="width:48px;height:48px;font-size:1.2rem;flex-shrink:0;">
                                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-width-0">
                                        <div class="fw-bold text-truncate" style="max-width: 150px;">{{ $user->name }}</div>
                                        @if($user->telegram_username)
                                            <small class="text-muted d-block text-truncate" style="max-width: 150px;">{{ '@'.$user->telegram_username }}</small>
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
                            <div class="col-12 col-lg-3">
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('users.show', $user) }}"
                                       class="btn btn-sm btn-outline-primary flex-fill">
                                        <i class="bi bi-eye me-1"></i> Просмотр
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('users.destroy', $user) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Вы уверены, что хотите удалить пользователя {{ $user->name }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-people fs-1 d-block mb-3"></i>
                        <p class="mb-0">Пользователей пока нет</p>
                        <a href="{{ route('users.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-lg me-1"></i> Добавить первого пользователя
                        </a>
                    </div>
                @endforelse
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection

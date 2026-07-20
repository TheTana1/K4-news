@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Главная</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Пользователи</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Три колонки сверху -->
        <div class="col-lg-12">
            <div class="row g-4">
                <!-- Колонка 1: Аватар + кнопки -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block mb-3">
                                @if($user->avatar_path)
                                    <img src="{{ asset($user->avatar_path) }}" alt="{{ $user->name }}"
                                         class="rounded-circle border border-3 border-white shadow" width="120"
                                         height="120" style="object-fit: cover;">
                                @else
                                    @php
                                        $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                                        $color = $colors[abs(crc32($user->name)) % count($colors)];
                                    @endphp
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white mx-auto bg-{{ $color }}"
                                         style="width:120px;height:120px;font-size:3rem;">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h4 class="mb-1">{{ $user->name }}</h4>
                            @if($user->telegram_username)
                                <p class="text-muted small">
                                    <i class="bi bi-telegram me-1"></i> @ {{ $user->telegram_username }}
                                </p>
                            @endif

                            <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                                @if($user->role)
                                    @php
                                        $roleColors = [
                                            'admin' => 'bg-purple-100 text-purple-800',
                                            'moderator' => 'bg-blue-100 text-blue-800',
                                            'user' => 'bg-gray-100 text-gray-800',
                                        ];
                                    @endphp
                                    <span class="badge {{ $roleColors[$user->role->slug] ?? $roleColors['user'] }}">
                                        {{ $user->role->label }}
                                    </span>
                                @endif
                                @if($user->is_active_in_group)
                                    <span class="badge bg-success">В группе</span>
                                @else
                                    <span class="badge bg-secondary">Не в группе</span>
                                @endif
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-success">
                                    <i class="bi bi-pencil me-1"></i> Редактировать
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                            onclick="return confirm('Удалить пользователя?')">
                                        <i class="bi bi-trash me-1"></i> Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Колонка 2: Контакты -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="bi bi-envelope me-1"></i> Контакты
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-envelope me-1"></i> Email
                                    </span>
                                    <a href="mailto:{{ $user->email }}" class="text-break">{{ $user->email }}</a>
                                </div>
                            </li>
                            @if($user->phones->isNotEmpty())
                                @foreach($user->phones as $phone)
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">
                                                <i class="bi bi-phone me-1"></i> Телефон
                                            </span>
                                            <div>
                                                <a href="tel:{{ $phone->phone_number }}">
                                                    {{ $phone->phone_number }}
                                                </a>
                                                @if($phone->is_primary)
                                                    <span class="badge bg-success ms-1">осн.</span>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                            @if($user->telegram_id)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="bi bi-telegram me-1"></i> Telegram ID
                                        </span>
                                        <span>{{ $user->telegram_id }}</span>
                                    </div>
                                </li>
                            @endif
                            @if($user->telegram_username)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="bi bi-at me-1"></i> Username
                                        </span>
                                        <span>@ {{ $user->telegram_username }}</span>
                                    </div>
                                </li>
                            @endif
                            @if($user->phones->isEmpty() && !$user->telegram_id && !$user->telegram_username)
                                <li class="list-group-item text-center text-muted py-4">
                                    <i class="bi bi-info-circle"></i> Нет контактов
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Колонка 3: Дополнительная информация -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="bi bi-info-circle me-1"></i> Дополнительно
                        </div>
                        <ul class="list-group list-group-flush">
                            @if($user->birthday)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-calendar me-1"></i> Дата рождения
                                    </span>
                                    <span>{{ \Carbon\Carbon::parse($user->birthday)->format('d.m.Y') }}</span>
                                </li>
                            @endif
                            @if(isset($user->gender))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-gender-ambiguous me-1"></i> Пол
                                    </span>
                                    <span>{{ $user->gender ? 'Женский' : 'Мужской' }}</span>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bi bi-clock-history me-1"></i> Зарегистрирован
                                </span>
                                <span>{{ $user->created_at->format('d.m.Y H:i') }}</span>
                            </li>
                            @if($user->joined_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-person-plus me-1"></i> Вступил в группу
                                    </span>
                                    <span>{{ \Carbon\Carbon::parse($user->joined_at)->format('d.m.Y H:i') }}</span>
                                </li>
                            @endif
                            @if($user->left_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-person-x me-1"></i> Покинул группу
                                    </span>
                                    <span>{{ \Carbon\Carbon::parse($user->left_at)->format('d.m.Y H:i') }}</span>
                                </li>
                            @endif
                            @if($user->last_post_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-chat-dots me-1"></i> Последний пост
                                    </span>
                                    <span>{{ \Carbon\Carbon::parse($user->last_post_at)->diffForHumans() }}</span>
                                </li>
                            @endif
                            @if($user->last_activity_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-activity me-1"></i> Последняя активность
                                    </span>
                                    <span>{{ \Carbon\Carbon::parse($user->last_activity_at)->diffForHumans() }}</span>
                                </li>
                            @endif
                            @if(!$user->birthday && !isset($user->gender) && !$user->joined_at && !$user->last_activity_at)
                                <li class="list-group-item text-center text-muted py-4">
                                    <i class="bi bi-info-circle"></i> Нет дополнительной информации
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Комментарии -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-chat me-1"></i> Последние комментарии
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ $comments->total() ?? $comments->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($comments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 40%">
                                        <i class="bi bi-chat-quote me-1"></i> Комментарий
                                    </th>
                                    <th style="width: 35%">
                                        <i class="bi bi-link-45deg me-1"></i> Источник
                                    </th>
                                    <th style="width: 25%" class="text-end">
                                        <i class="bi bi-clock me-1"></i> Дата
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($comments as $comment)
                                    <tr>
                                        <td class="align-middle">
                                            <div class="text-truncate" style="max-width: 350px;"
                                                 title="{{ $comment->comment }}">
                                                {{ Str::limit($comment->comment, 100) }}
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <a href="{{ route('comments.show', $comment) }}"
                                               class="text-decoration-none text-dark"
                                               title="{{ $comment->source }}">
                                                {{ Str::limit($comment->source, 80) }}
                                            </a>
                                        </td>
                                        <td class="align-middle text-end text-nowrap">
                                            <small>
                                                <i class="bi bi-calendar3 text-muted me-1"></i>
                                                {{ $comment->created_at->format('d.m.Y') }}
                                                <i class="bi bi-clock text-muted ms-1 me-1"></i>
                                                {{ $comment->created_at->format('H:i') }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Пагинация --}}
                        @if($comments->hasPages())
                            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                                <div class="small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Показано {{ $comments->firstItem() ?? 0 }} - {{ $comments->lastItem() ?? 0 }}
                                    из {{ $comments->total() ?? $comments->count() }}
                                </div>
                                <div>
                                    {{ $comments->links() }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots display-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-0">Нет комментариев</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

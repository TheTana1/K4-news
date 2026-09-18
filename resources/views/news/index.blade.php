@extends('layouts.app')

@section('title', 'Новости')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Новости</h1>
        <a href="{{ route('news.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Добавить новость
        </a>
    </div>
    <!-- Фильтр -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('news.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Content -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Содержимое</label>
                        <input type="text"
                               name="content"
                               class="form-control form-control-sm"
                               placeholder="Поиск по содержимому"
                               value="{{ request('content') }}">
                    </div>

                    <!-- Author -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Автор</label>
                        <input type="text"
                               name="author"
                               class="form-control form-control-sm"
                               placeholder="Поиск по автору"
                               value="{{ request('author') }}">
                    </div>

                    <!-- Роль -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Роль</label>
                        <select name="role_id" class="form-select form-select-sm">
                            <option value="">Все роли</option>
                            <option value="2">Всем</option>
                            <option value="3">Сотрудникам Кухни</option>
                            <option value="4">Сотрудникам Зала</option>
                        </select>
                    </div>

                    <!-- Статус -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Статус</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Все статусы</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                                Активные
                            </option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                                Не активные
                            </option>
                        </select>
                    </div>

                    <!-- Дата от -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Дата от</label>
                        <input type="date"
                               name="date_from"
                               class="form-control form-control-sm"
                               value="{{ request('date_from') }}">
                    </div>

                    <!-- Дата до -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Дата до</label>
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
                            <a href="{{ route('news.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Статистика -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-newspaper fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Всего</div>
                        <div class="h4 mb-0">{{ $news->total() }}</div>
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
                        <div class="text-muted small text-uppercase">Активных</div>
                        <div class="h4 mb-0">{{ $news->where('status', 'active')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-secondary bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-clock fs-4 text-secondary"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Неактив</div>
                        <div class="h4 mb-0">{{ $news->where('status', '!=', 'active')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-calendar fs-4 text-info"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">За месяц</div>
                        <div
                            class="h4 mb-0">{{ $news->where('created_at', '>=', now()->subMonth())->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Список объявлений -->
    <div class="card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($news as $n)
                    <div class="list-group-item p-3">
                        <div class="row g-3">
                            <div class="col-12 col-lg-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @php
                                            $content = $n->content;
                                            $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                                            $color = $colors[abs(crc32($content)) % count($colors)];
                                        @endphp
                                        <div
                                            class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-{{ $color }}"
                                            style="width:48px;height:48px;font-size:1.2rem;flex-shrink:0;">
                                            {{ mb_strtoupper(mb_substr($content, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="min-width-0">
                                        <div class="fw-bold text-truncate" style="max-width: 150px;">
                                            {{ $n->content }}
                                        </div>
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
                                                $spanLabel = match($n->role?->slug){
                                                    'moderator'=> 'Всем',
                                                    'Kitchen_Worker' => 'Сотрудникам Кухни',
                                                    'Service Staff'=>'Сотрудникам Зала'
                                                };
                                                 $spanColor = match($n->role?->slug){
                                                    'moderator'=> "#0D6EFD",
                                                    'Kitchen_Worker' => "#a40e13",
                                                    'Service Staff'=> "#ff661b"
                                                };
                                            @endphp
                                            <span
                                                style="color: {{ $spanColor }}">{{ $spanLabel }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <small class="text-muted d-block">Статус</small>
                                            @if($n->status === 'active')
                                                <span class="badge bg-success">Активно</span>
                                            @else
                                                <span class="badge bg-secondary">Не активно</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <small class="text-muted d-block">Дата публикации</small>
                                            <span class="small">{{ local_date($n->published_at) }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Действия -->
                            @auth()
                            <div class="col-12 col-lg-3">
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('news.show', $n) }}"
                                       class="btn btn-sm btn-outline-primary flex-fill">
                                        <i class="bi bi-eye me-1"></i> Просмотр
                                    </a>
                                    @can('update', $n)
                                        <a href="{{ route('news.edit',  $n) }}"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $n)
                                        <form action="{{ route('news.destroy', $n) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Вы уверены, что хотите удалить объявление #{{ $n->id }}?')">
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
                        <i class="bi bi-megaphone fs-1 d-block mb-3"></i>
                        <p class="mb-0">Объявлений пока нет</p>
                        <a href="{{ route('news.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-lg me-1"></i> Добавить первое объявление
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        @if($news->hasPages())
            <div class="card-footer">
                {{ $news->links() }}
            </div>
        @endif
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Объявления')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Объявления</h1>
        <a href="{{ route('advertisements.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Добавить объявление
        </a>
    </div>

    <!-- Статистика -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-megaphone fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Всего</div>
                        <div class="h4 mb-0">{{ $advertisements->total() }}</div>
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
                        <div class="h4 mb-0">{{ $advertisements->where('status', 'active')->count() }}</div>
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
                        <div class="h4 mb-0">{{ $advertisements->where('status', '!=', 'active')->count() }}</div>
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
                            class="h4 mb-0">{{ $advertisements->where('created_at', '>=', now()->subMonth())->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Список объявлений -->
    <div class="card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($advertisements as $ad)
                    <div class="list-group-item p-3">
                        <div class="row g-3">
                            <div class="col-12 col-lg-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @php
                                            $content = $ad->content;
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
                                            {{ $ad->content }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Информация -->
                            <div class="col-12 col-lg-6">
                                <div class="row g-2">
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <small class="text-muted d-block">Статус</small>
                                            @if($ad->status === 'active')
                                                <span class="badge bg-success">Активно</span>
                                            @else
                                                <span class="badge bg-secondary">Не активно</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <small class="text-muted d-block">Дата создания</small>
                                            <span class="small">{{ $ad->created_at->format('d.m.Y') }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Действия -->
                            <div class="col-12 col-lg-3">
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('advertisements.show', $ad) }}"
                                       class="btn btn-sm btn-outline-primary flex-fill">
                                        <i class="bi bi-eye me-1"></i> Просмотр
                                    </a>
                                    <a href="{{ route('advertisements.edit', $ad) }}"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('advertisements.destroy', $ad) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Вы уверены, что хотите удалить объявление #{{ $ad->id }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-megaphone fs-1 d-block mb-3"></i>
                        <p class="mb-0">Объявлений пока нет</p>
                        <a href="{{ route('advertisements.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-lg me-1"></i> Добавить первое объявление
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        @if($advertisements->hasPages())
            <div class="card-footer">
                {{ $advertisements->links() }}
            </div>
        @endif
    </div>
@endsection

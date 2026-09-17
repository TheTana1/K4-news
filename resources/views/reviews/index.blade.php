@extends('layouts.app')

@section('title', 'Отзывы')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Отзывы</h1>
    </div>
    <!-- Фильтр -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reviews.index') }}" id="filterForm">
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

{{--                    <!-- Author -->--}}
{{--                    <div class="col-12 col-md-6 col-lg-2">--}}
{{--                        <label class="form-label small text-muted">Автор</label>--}}
{{--                        <input type="text"--}}
{{--                               name="author"--}}
{{--                               class="form-control form-control-sm"--}}
{{--                               placeholder="Поиск по автору"--}}
{{--                               value="{{ request('author') }}">--}}
{{--                    </div>--}}

                    <!-- Rating -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small text-muted">Рейтинг</label>
                        <select name="rating" class="form-select form-select-sm">
                            <option value="">Все отзывы</option>
                            <option value="1">★</option>
                            <option value="2">★★</option>
                            <option value="3">★★★</option>
                            <option value="4">★★★★</option>
                            <option value="5">★★★★★</option>
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
                            <a href="{{ route('reviews.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        @forelse($reviews as $review)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm hover-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="card-title mb-0">
                                    {{ $review->user?->name ?? $review->author_name ?? 'Гость' }}
                                </h5>
                                <small class="text-muted">{{ $review->created_at->format('d.m.Y') }}</small>
                            </div>
                            @if($review->rating)
                                <div class="text-nowrap">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-warning" style="font-size: 1.1rem;">
                                            {{ $i <= $review->rating ? '★' : '☆' }}
                                        </span>
                                    @endfor
                                </div>
                            @endif
                        </div>

                        <p class="card-text">{{ Str::limit($review->content, 120) }}</p>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <a href="{{ route('reviews.show', $review) }}" class="btn btn-sm btn-outline-primary">
                                Читать полностью →
                            </a>
                            @can('delete' , $review)
                                <div class="d-inline-flex gap-1">
                                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить отзыв?')">Уд.</button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                Отзывов пока нет
            </div>
        @endforelse
    </div>

    @if($reviews->hasPages())
        <div class="card-footer">
            {{ $reviews->links() }}
        </div>
    @endif
@endsection

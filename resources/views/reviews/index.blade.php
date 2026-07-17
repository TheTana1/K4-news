@extends('layouts.app')

@section('title', 'Отзывы')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Отзывы</h1>
        <a href="{{ route('reviews.create') }}" class="btn btn-primary">
            + Оставить отзыв
        </a>
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
                            @auth
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('reviews.edit', $review) }}" class="btn btn-sm btn-outline-success">Ред.</a>
                                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить отзыв?')">Уд.</button>
                                    </form>
                                </div>
                            @endauth
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
        <div class="d-flex justify-content-center mt-4">
            {{ $reviews->links() }}
        </div>
    @endif
@endsection

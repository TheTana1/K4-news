@extends('layouts.app')

@section('title', 'Главная панель')

@section('content')
    <!-- Приветствие -->
    <div class="bg-primary bg-gradient text-white rounded-3 p-4 mb-4 shadow">
        @php
            $hour = date('H');
            $greeting = match (true) {
                $hour >= 5 && $hour < 12 => 'Доброе утро',
                $hour >= 12 && $hour < 18 => 'Добрый день',
                $hour >= 18 && $hour < 23 => 'Добрый вечер',
                default => 'Доброй ночи',
            };
        @endphp
        <h1 class="h3 fw-bold mb-1">{{ $greeting }}, {{ Auth::user()->name ?? 'Гость' }}!</h1>
        <p class="text-white-50 mb-0">Сегодня {{ now()->format('d.m.Y') }}</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-megaphone fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Важное</div>
                        <div class="h4 mb-0">{{ $stats['ads_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-newspaper fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Новости</div>
                        <div class="h4 mb-0">{{ $stats['news_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-star fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Отзывы</div>
                        <div class="h4 mb-0">{{ $stats['reviews_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-people fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Коллеги</div>
                        <div class="h4 mb-0">{{ $stats['users_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Последние записи -->
    <div class="row row-cols-1 row-cols-lg-3 g-4">
        <!-- Последние объявления -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-megaphone me-1"></i> Последние важные объявления
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentAds ?? [] as $ad)
                            <li class="list-group-item">
                                <a href="{{ route('advertisements.show', $ad) }}" class="text-primary fw-semibold text-decoration-none">
                                    <div>
                                        {{ Str::limit($ad->content, 50) }}
                                    </div>
                                    <div class="small text-muted">{{ $ad->created_at->diffForHumans() }}</div>
                                </a>

                            </li>
                        @empty
                            <li class="list-group-item text-muted">Нет объявлений</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Последние новости -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-newspaper me-1"></i> Последние новости
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentNews ?? [] as $news)
                            <li class="list-group-item">
                                <a href="{{ route('news.show', $news) }}" class="text-success fw-semibold text-decoration-none">
                                    <div>
                                        {{ Str::limit($news->content, 50) }}
                                        <div class="small text-muted">{{ $news->created_at->diffForHumans() }}</div>
                                    </div>
                                </a>

                            </li>
                        @empty
                            <li class="list-group-item text-muted">Нет новостей</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Последние отзывы -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-star me-1"></i> Последние отзывы
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentReviews ?? [] as $review)
                            <li class="list-group-item">

                                <a href="{{ route('reviews.show', $review) }}" class="text-black fw-semibold text-decoration-none">
                                    <div>
                                        {{ Str::limit($review->content, 50) }}
                                        <div class="small text-muted">{{ $news->created_at->diffForHumans() }}</div>
                                    </div>
                                </a>

                            </li>
                        @empty
                            <li class="list-group-item text-muted">Нет отзывов</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

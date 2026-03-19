@extends('layouts.main')

@section('title', 'Главная панель')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Приветствие -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl p-8 mb-8 text-white">
            @php
                $hour = date('H');
                $greeting = match (true) {
                    $hour >= 5 && $hour < 12 => 'Доброе утро',
                    $hour >= 12 && $hour < 18 => 'Добрый день',
                    $hour >= 18 && $hour < 23 => 'Добрый вечер',
                    default => 'Доброй ночи',
                };
            @endphp
            <h1 class="text-3xl font-bold mb-2">{{ $greeting }}, {{ Auth::user()->name ?? 'Гость' }}!</h1>
            <p class="text-blue-100">Сегодня {{ now()->format('d.m.Y') }}</p>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-3 mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Объявления</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['ads_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3 mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Новости</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['news_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-center">
                    <div class="bg-yellow-100 rounded-full p-3 mr-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Отзывы</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['reviews_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3 mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Пользователи</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['users_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Последние записи -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Последние объявления -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Последние объявления</h3>
                <div class="space-y-3">
                    @forelse($recentAds ?? [] as $ad)
                        <div class="border-b border-gray-100 pb-2 last:border-0">
                            <a href="{{ route('advertisements.show', $ad) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ mb_substr($ad->content,0,30) }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $ad->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Нет объявлений</p>
                    @endforelse
                </div>
            </div>

            <!-- Последние новости -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Последние новости</h3>
                <div class="space-y-3">
                    @forelse($recentNews ?? [] as $news)
                        <div class="border-b border-gray-100 pb-2 last:border-0">
                            <a href="{{ route('news.show', $news) }}" class="text-green-600 hover:text-green-800 font-medium">
                                {{ mb_substr($news->content,0 ,30) }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $news->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Нет новостей</p>
                    @endforelse
                </div>
            </div>

            <!-- Последние отзывы -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Последние отзывы</h3>
                <div class="space-y-3">
                    @forelse($recentReviews ?? [] as $review)
                        <div class="border-b border-gray-100 pb-2 last:border-0">
                            <p class="text-gray-800">{{ Str::limit($review->content, 50) }}</p>
                            <div class="flex items-center text-sm text-gray-500">
                                <span>{{ $review->user?->name ?? 'Гость' }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Нет отзывов</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

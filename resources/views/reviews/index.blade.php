@extends('layouts.main')

@section('title', 'Отзывы')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Заголовок -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Отзывы</h1>
            <a href="{{ route('reviews.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                + Оставить отзыв
            </a>
        </div>

        <!-- Список отзывов -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($reviews as $review)
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $review->user?->name ?? $review->author_name ?? 'Гость' }}</h3>
                                <p class="text-sm text-gray-500">{{ $review->created_at->format('d.m.Y') }}</p>
                            </div>
                            @if($review->rating)
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            @endif
                        </div>

                        <p class="text-gray-700 mb-4">{{ Str::limit($review->content, 150) }}</p>

                        <div class="flex justify-between items-center">
                            <a href="{{ route('reviews.show', $review) }}" class="text-blue-600 hover:text-blue-800 text-sm">Читать полностью →</a>

                            @auth
                                <div class="flex space-x-2">
                                    <a href="{{ route('reviews.edit', $review) }}" class="text-green-600 hover:text-green-800 text-sm">Ред.</a>
                                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Удалить отзыв?')">Уд.</button>
                                    </form>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    Отзывов пока нет
                </div>
            @endforelse
        </div>

        @if($reviews->hasPages())
            <div class="mt-6">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
@endsection

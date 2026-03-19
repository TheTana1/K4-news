@extends('layouts.main')

@section('title', 'Отзыв')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Навигация -->
        <nav class="flex mb-6 text-sm">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Главная</a>
            <span class="mx-2 text-gray-500">/</span>
            <a href="{{ route('reviews.index') }}" class="text-gray-500 hover:text-gray-700">Отзывы</a>
            <span class="mx-2 text-gray-500">/</span>
            <span class="text-gray-900">Отзыв #{{ $review->id }}</span>
        </nav>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Отзыв</h1>
                @auth
                    <div class="flex space-x-2">
                        <a href="{{ route('reviews.edit', $review) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            Редактировать
                        </a>
                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm" onclick="return confirm('Удалить отзыв?')">
                                Удалить
                            </button>
                        </form>
                    </div>
                @endauth
            </div>

            <div class="p-6">
                <!-- Информация об авторе -->
                <div class="flex items-start mb-6">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <h2 class="text-xl font-semibold text-gray-900">
                                {{ $review->user?->name ?? $review->author_name ?? 'Гость' }}
                            </h2>
                            @if($review->user && $review->user->is_active_in_group)
                                <span class="ml-3 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">В группе</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">{{ $review->created_at->format('d.m.Y H:i') }}</p>
                    </div>

                    @if($review->rating)
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-6 h-6 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    @endif
                </div>

                <!-- Текст отзыва -->
                <div class="prose max-w-none mb-8">
                    {!! nl2br(e($review->content)) !!}
                </div>

                <!-- Комментарии -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Комментарии</h3>

                    @auth
                        <form action="{{ route('comments.store') }}" method="POST" class="mb-6">
                            @csrf
                            <input type="hidden" name="commentable_id" value="{{ $review->id }}">
                            <input type="hidden" name="commentable_type" value="review">
                            <textarea name="comment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ваш комментарий..."></textarea>
                            <button type="submit" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                Отправить
                            </button>
                        </form>
                    @endauth

                    <div class="space-y-4">
                        @forelse($review->comments as $comment)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $comment->user?->name ?? 'Гость' }}</span>
                                        <span class="text-sm text-gray-500 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    @can('delete', $comment)
                                        <form action="{{ route('comments.destroy', $comment) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Удалить</button>
                                        </form>
                                    @endcan
                                </div>
                                <p class="mt-2 text-gray-700">{{ $comment->comment }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500">Комментариев пока нет</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

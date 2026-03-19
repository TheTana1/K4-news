@extends('layouts.main')

@section('title', substr($news->content,0,10))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Навигация -->
        <nav class="flex mb-6 text-sm">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Главная</a>
            <span class="mx-2 text-gray-500">/</span>
            <a href="{{ route('news.index') }}" class="text-gray-500 hover:text-gray-700">Новости</a>
            <span class="mx-2 text-gray-500">/</span>
        </nav>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <div class="flex space-x-2">
                    <a href="{{ route('news.edit', $news) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                        Редактировать
                    </a>
                    <form action="{{ route('news.destroy', $news) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm" onclick="return confirm('Удалить новость?')">
                            Удалить
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-6">
                <!-- Мета-информация -->
                <div class="flex items-center text-sm text-gray-500 mb-6 pb-6 border-b border-gray-200">
                    <span class="mr-4">Автор: {{ $news->author?->name ?? $news->telegram_author_name ?? 'Неизвестно' }}</span>
                    <span class="mr-4">📅 {{ $news->created_at->format('d.m.Y H:i') }}</span>
                    <span>👁 {{ $news->views ?? 0 }} просмотров</span>
                </div>

                <!-- Содержимое -->
                <div class="prose max-w-none ">
                    {!! nl2br(e($news->content)) !!}
                </div>

                <!-- Изображение -->
                @if($news->image_path)
                    <div class="mt-6">
                        <img src="{{ asset($news->image_path) }}" alt="{{ $news->title }}" class="rounded-lg max-w-full h-auto">
                    </div>
                @endif

                <!-- Комментарии -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Комментарии</h2>

                    @auth
                        <form action="{{ route('comments.store') }}" method="POST" class="mb-6">
                            @csrf
                            <input type="hidden" name="commentable_id" value="{{ $news->id }}">
                            <input type="hidden" name="commentable_type" value="news">
                            <textarea name="comment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ваш комментарий..."></textarea>
                            <button type="submit" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                Отправить
                            </button>
                        </form>
                    @endauth

                    <div class="space-y-4">
                        @forelse($news->comments as $comment)
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

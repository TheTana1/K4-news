@extends('layouts.main')

@section('title', $advertisement->title)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Навигация -->
        <nav class="flex mb-6 text-sm">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Главная</a>
            <span class="mx-2 text-gray-500">/</span>
            <a href="{{ route('advertisements.index') }}" class="text-gray-500 hover:text-gray-700">Объявления</a>
            <span class="mx-2 text-gray-500">/</span>
            <span class="text-gray-900">{{ $advertisement->title }}</span>
        </nav>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">{{ $advertisement->title }}</h1>
                <div class="flex space-x-2">
                    <a href="{{ route('advertisements.edit', $advertisement) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                        Редактировать
                    </a>
                    <form action="{{ route('advertisements.destroy', $advertisement) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm" onclick="return confirm('Удалить?')">
                            Удалить
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-6">
                <!-- Информация -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <!-- Содержимое -->
                        <div class="prose max-w-none">
                            {!! nl2br(e($advertisement->content)) !!}
                        </div>

                        <!-- Цена -->
                        @if($advertisement->price)
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                <span class="text-lg font-semibold text-blue-900">Цена: {{ number_format($advertisement->price, 0, ',', ' ') }} ₽</span>
                            </div>
                        @endif
                    </div>

                    <!-- Боковая панель -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-700 mb-2">Информация</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Автор:</dt>
                                    <dd class="text-gray-900">{{ $advertisement->author?->name ?? $advertisement->telegram_author_name ?? 'Неизвестно' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Статус:</dt>
                                    <dd>
                                        @if($advertisement->status === 'active')
                                            <span class="text-green-600">Активно</span>
                                        @else
                                            <span class="text-gray-600">Не активно</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Просмотров:</dt>
                                    <dd class="text-gray-900">{{ $advertisement->views ?? 0 }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Создано:</dt>
                                    <dd class="text-gray-900">{{ $advertisement->created_at->format('d.m.Y H:i') }}</dd>
                                </div>
                                @if($advertisement->published_at)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Опубликовано:</dt>
                                        <dd class="text-gray-900">{{ $advertisement->published_at->format('d.m.Y H:i') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        @if($advertisement->city)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-gray-700 mb-2">Местоположение</h3>
                                <p class="text-gray-900">{{ $advertisement->city }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Комментарии -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Комментарии</h2>

                    @auth
                        <form action="{{ route('comments.store') }}" method="POST" class="mb-6">
                            @csrf
                            <input type="hidden" name="commentable_id" value="{{ $advertisement->id }}">
                            <input type="hidden" name="commentable_type" value="advertisement">
                            <textarea name="comment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ваш комментарий..."></textarea>
                            <button type="submit" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                Отправить
                            </button>
                        </form>
                    @endauth

                    <div class="space-y-4">
                        @forelse($advertisement->comments as $comment)
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

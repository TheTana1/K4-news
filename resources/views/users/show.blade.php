@extends('layouts.main')

@section('title', $user->name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Навигация -->
        <nav class="flex mb-6 text-sm">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Главная</a>
            <span class="mx-2 text-gray-500">/</span>
            <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-gray-700">Пользователи</a>
            <span class="mx-2 text-gray-500">/</span>
            <span class="text-gray-900">{{ $user->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Левая колонка - Профиль -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-32"></div>
                    <div class="px-6 pb-6 text-center relative">
                        <div class="flex justify-center -mt-16 mb-4">
                            @if($user->avatar_path)
                                <img class="h-24 w-24 rounded-full border-4 border-white shadow-lg object-cover" src="{{ asset($user->avatar_path) }}" alt="">
                            @else
                                <div class="h-24 w-24 rounded-full border-4 border-white shadow-lg bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>

                        @if($user->telegram_username)
                            <p class="text-sm text-gray-500 mb-2">@ {{ $user->telegram_username }}</p>
                        @endif

                        <div class="flex justify-center space-x-2 mb-4">
                            @if($user->role)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                @if($user->role->slug === 'admin') bg-purple-100 text-purple-800
                                @elseif($user->role->slug === 'moderator') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $user->role->label }}
                            </span>
                            @endif

                            @if($user->is_active_in_group)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                В группе
                            </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                Не в группе
                            </span>
                            @endif
                        </div>

                        <!-- Статистика -->
                        <div class="grid grid-cols-3 gap-2 border-t border-gray-100 pt-4">
                            <div>
                                <div class="text-xl font-bold text-gray-900">{{ $user->advertisements_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Объявлений</div>
                            </div>
                            <div>
                                <div class="text-xl font-bold text-gray-900">{{ $user->comments_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Комментариев</div>
                            </div>
                            <div>
                                <div class="text-xl font-bold text-gray-900">{{ $user->likes ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Лайков</div>
                            </div>
                        </div>

                        <!-- Действия -->
                        <div class="mt-4 flex justify-center space-x-2">
                            <a href="{{ route('users.edit', $user) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                                Редактировать
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm" onclick="return confirm('Удалить пользователя?')">
                                    Удалить
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Контактная информация -->
                <div class="bg-white rounded-xl shadow-md p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Контактная информация
                    </h3>

                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-8 text-gray-400">📧</div>
                            <div>
                                <div class="text-sm text-gray-500">Email</div>
                                <a href="mailto:{{ $user->email }}" class="text-gray-900 hover:text-blue-600">{{ $user->email }}</a>
                            </div>
                        </div>

                        @if($user->phones->count() > 0)
                            @foreach($user->phones as $phone)
                                <div class="flex items-start">
                                    <div class="w-8 text-gray-400">📞</div>
                                    <div>
                                        <div class="text-sm text-gray-500">Телефон</div>
                                        <a href="tel:{{ $phone->phone_number }}" class="text-gray-900 hover:text-blue-600">
                                            {{ $phone->phone_number }}
                                            @if($phone->is_primary) <span class="text-xs text-green-600">(основной)</span> @endif
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if($user->telegram_id)
                            <div class="flex items-start">
                                <div class="w-8 text-gray-400">✈️</div>
                                <div>
                                    <div class="text-sm text-gray-500">Telegram ID</div>
                                    <div class="text-gray-900">{{ $user->telegram_id }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Дополнительная информация -->
                <div class="bg-white rounded-xl shadow-md p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Дополнительно</h3>

                    <dl class="space-y-2 text-sm">
                        @if($user->birthday)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Дата рождения:</dt>
                                <dd class="text-gray-900">{{ $user->birthday->format('d.m.Y') }}</dd>
                            </div>
                        @endif

                        @if(isset($user->gender))
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Пол:</dt>
                                <dd class="text-gray-900">{{ $user->gender ? 'Женский' : 'Мужской' }}</dd>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <dt class="text-gray-500">Зарегистрирован:</dt>
                            <dd class="text-gray-900">{{ $user->created_at->format('d.m.Y') }}</dd>
                        </div>

                        @if($user->joined_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Вступил в группу:</dt>
                                <dd class="text-gray-900">{{ $user->joined_at->format('d.m.Y H:i') }}</dd>
                            </div>
                        @endif

                        @if($user->left_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Покинул группу:</dt>
                                <dd class="text-gray-900">{{ $user->left_at->format('d.m.Y H:i') }}</dd>
                            </div>
                        @endif

                        @if($user->last_post_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Последний пост:</dt>
                                <dd class="text-gray-900">{{ $user->last_post_at->diffForHumans() }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Правая колонка - Активность -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Последние объявления -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Последние объявления</h3>

                    @if($user->advertisements->count() > 0)
                        <div class="space-y-3">
                            @foreach($user->advertisements->take(5) as $ad)
                                <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                    <a href="{{ route('advertisements.show', $ad) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ $ad->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">{{ $ad->created_at->format('d.m.Y H:i') }}</p>
                                    <p class="text-sm text-gray-700">{{ Str::limit($ad->content, 100) }}</p>
                                </div>
                            @endforeach
                        </div>

                        @if($user->advertisements->count() > 5)
                            <div class="mt-3 text-right">
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Все объявления →</a>
                            </div>
                        @endif
                    @else
                        <p class="text-gray-500">Нет объявлений</p>
                    @endif
                </div>

                <!-- Последние новости -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Последние новости</h3>

                    @if($user->news->count() > 0)
                        <div class="space-y-3">
                            @foreach($user->news->take(5) as $news)
                                <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                    <a href="{{ route('news.show', $news) }}" class="text-green-600 hover:text-green-800 font-medium">
                                        {{ $news->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">{{ $news->created_at->format('d.m.Y H:i') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Нет новостей</p>
                    @endif
                </div>

{{--                <!-- Последние отзывы -->--}}
{{--                <div class="bg-white rounded-xl shadow-md p-6">--}}
{{--                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Последние отзывы</h3>--}}

{{--                    @if($user->reviews->count() > 0)--}}
{{--                        <div class="space-y-3">--}}
{{--                            @foreach($user->reviews->take(5) as $review)--}}
{{--                                <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">--}}
{{--                                    <div class="flex items-center mb-1">--}}
{{--                                        <div class="flex mr-2">--}}
{{--                                            @for($i = 1; $i <= 5; $i++)--}}
{{--                                                <svg class="w-4 h-4 {{ $i <= ($review->rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">--}}
{{--                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>--}}
{{--                                                </svg>--}}
{{--                                            @endfor--}}
{{--                                        </div>--}}
{{--                                        <span class="text-sm text-gray-500">{{ $review->created_at->format('d.m.Y') }}</span>--}}
{{--                                    </div>--}}
{{--                                    <p class="text-sm text-gray-700">{{ Str::limit($review->content, 150) }}</p>--}}
{{--                                    <a href="{{ route('reviews.show', $review) }}" class="text-xs text-blue-600 hover:text-blue-800 mt-1 inline-block">Подробнее</a>--}}
{{--                                </div>--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    @else--}}
{{--                        <p class="text-gray-500">Нет отзывов</p>--}}
{{--                    @endif--}}
{{--                </div>--}}

                <!-- Последние комментарии -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Последние комментарии</h3>

                    @if($user->comments->count() > 0)
                        <div class="space-y-3">
                            @foreach($user->comments->take(5) as $comment)
                                <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                    <p class="text-sm text-gray-700">{{ Str::limit($comment->comment, 100) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $comment->created_at->format('d.m.Y H:i') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Нет комментариев</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

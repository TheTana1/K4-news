@extends('layouts.main')

@section('title', 'Главная панель')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Приветствие с временем суток -->
        @php
            $hour = date('H');
            if ($hour >= 5 && $hour < 12) {
                $greeting = 'Доброе утро';
            } elseif ($hour >= 12 && $hour < 18) {
                $greeting = 'Добрый день';
            } elseif ($hour >= 18 && $hour < 23) {
                $greeting = 'Добрый вечер';
            } else {
                $greeting = 'Доброй ночи';
            }
        @endphp

        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl p-8 mb-8 text-white">
            <h1 class="text-3xl font-bold mb-2">{{ $greeting }}, {{ Auth::user()->name ?? 'Гость' }}!</h1>
            <p class="text-blue-100">Сегодня {{ now()->format('d.m.Y') }}</p>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Объявления -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-green-600 bg-green-100 px-3 py-1 rounded-full">+3 новых</span>
                </div>
                <h3 class="text-gray-600 text-sm mb-1">Объявления</h3>
                <p class="text-3xl font-bold text-gray-800">12</p>
            </div>

            <!-- Новости -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-green-600 bg-green-100 px-3 py-1 rounded-full">+2 сегодня</span>
                </div>
                <h3 class="text-gray-600 text-sm mb-1">Новости</h3>
                <p class="text-3xl font-bold text-gray-800">8</p>
            </div>

            <!-- Отзывы -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-green-600 bg-green-100 px-3 py-1 rounded-full">+5 новых</span>
                </div>
                <h3 class="text-gray-600 text-sm mb-1">Отзывы</h3>
                <p class="text-3xl font-bold text-gray-800">45</p>
            </div>

            <!-- Сотрудники -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-blue-600 bg-blue-100 px-3 py-1 rounded-full">18 активных</span>
                </div>
                <h3 class="text-gray-600 text-sm mb-1">Сотрудники</h3>
                <p class="text-3xl font-bold text-gray-800">24</p>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Быстрые действия</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('advertisements.create') }}" class="bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg p-4 text-center transition-all duration-300 group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600">Добавить объявление</span>
                </a>

                <a href="{{ route('news.create') }}" class="bg-gray-50 hover:bg-green-50 border border-gray-200 hover:border-green-300 rounded-lg p-4 text-center transition-all duration-300 group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-600 group-hover:text-green-600">Добавить новость</span>
                </a>

                <a href="{{ route('reviews.create') }}" class="bg-gray-50 hover:bg-yellow-50 border border-gray-200 hover:border-yellow-300 rounded-lg p-4 text-center transition-all duration-300 group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-yellow-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-600 group-hover:text-yellow-600">Оставить отзыв</span>
                </a>

                <a href="{{ route('users.create') }}" class="bg-gray-50 hover:bg-purple-50 border border-gray-200 hover:border-purple-300 rounded-lg p-4 text-center transition-all duration-300 group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-600 group-hover:text-purple-600">Добавить сотрудника</span>
                </a>
            </div>
        </div>

        <!-- Последние действия и график посещаемости -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Последние действия -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Последние действия</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="bg-blue-100 p-2 rounded-full">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Добавлено новое объявление</p>
                                <p class="text-xs text-gray-500">14:30</p>
                            </div>
                        </div>
                        <span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full">Новое</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="bg-green-100 p-2 rounded-full">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Обновлена информация о сотруднике</p>
                                <p class="text-xs text-gray-500">13:15</p>
                            </div>
                        </div>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Обновление</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="bg-yellow-100 p-2 rounded-full">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Опубликована новость</p>
                                <p class="text-xs text-gray-500">11:20</p>
                            </div>
                        </div>
                        <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full">Публикация</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="bg-purple-100 p-2 rounded-full">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Добавлен новый отзыв</p>
                                <p class="text-xs text-gray-500">09:45</p>
                            </div>
                        </div>
                        <span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full">Новое</span>
                    </div>
                </div>

                <button class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    Показать все действия
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <!-- График посещаемости и активность -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Активность за неделю</h2>

                <!-- Статистика по дням -->
                <div class="space-y-3 mb-6">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Пн</span>
                            <span class="text-gray-800 font-medium">45</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 90%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Вт</span>
                            <span class="text-gray-800 font-medium">38</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 76%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Ср</span>
                            <span class="text-gray-800 font-medium">52</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Чт</span>
                            <span class="text-gray-800 font-medium">41</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 82%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Пт</span>
                            <span class="text-gray-800 font-medium">35</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 70%"></div>
                        </div>
                    </div>
                </div>

                <!-- Общая статистика -->
                <div class="border-t pt-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Всего просмотров:</span>
                        <span class="font-bold text-gray-800">211</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Среднее в день:</span>
                        <span class="font-bold text-gray-800">42.2</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Активных пользователей:</span>
                        <span class="font-bold text-gray-800">156</span>
                    </div>
                </div>

                <!-- Круговая диаграмма (процентное соотношение) -->
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Распределение по разделам</h3>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-600 flex-1">Объявления</span>
                            <span class="text-sm font-medium text-gray-800">34%</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-600 flex-1">Новости</span>
                            <span class="text-sm font-medium text-gray-800">22%</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-600 flex-1">Отзывы</span>
                            <span class="text-sm font-medium text-gray-800">28%</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-600 flex-1">Сотрудники</span>
                            <span class="text-sm font-medium text-gray-800">16%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

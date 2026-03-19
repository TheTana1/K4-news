<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">
<div class="min-h-screen flex flex-col">
    <!-- Навигация -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Логотип -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                            {{ config('app.name') }}
                        </a>
                    </div>

                    <!-- Навигационные ссылки -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-4">
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                            Главная
                        </a>

                        <a href="{{ route('advertisements.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('advertisements.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                            Объявления
                        </a>

                        <a href="{{ route('news.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('news.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                            Новости
                        </a>

                        <a href="{{ route('reviews.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('reviews.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                            Отзывы
                        </a>

                        <a href="{{ route('users.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                            Пользователи
                        </a>
                    </div>
                </div>

                <!-- Профиль -->
                <div class="flex items-center">
                    @auth
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-700">{{ Auth::user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                        Выйти
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">Вход</a>
                        <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 hover:text-gray-900">Регистрация</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Футер -->
    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Все права защищены.
            </p>
        </div>
    </footer>
</div>

@stack('scripts')
</body>
</html>

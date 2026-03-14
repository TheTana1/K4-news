<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Мой сайт')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<nav class="bg-gray-900 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <div class="flex space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        Главная
                    </a>
                    <a href="{{ route('advertisements.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('advertisements.*') ? 'bg-gray-700 text-white' : '' }}">
                        Объявления
                    </a>
                    <a href="{{ route('news.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('news.*') ? 'bg-gray-700 text-white' : '' }}">
                        Новости
                    </a>
                    <a href="{{ route('reviews.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reviews.*') ? 'bg-gray-700 text-white' : '' }}">
                        Отзывы
                    </a>
                    <a href="{{ route('users.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('employees.*') ? 'bg-gray-700 text-white' : '' }}">
                        Сотрудники
                    </a>
                </div>
            </div>

            <!-- Профиль пользователя (опционально) -->
            <div class="flex items-center space-x-3">
                <span class="text-gray-300 text-sm">{{ Auth::user()->name ?? 'Гость' }}</span>
                <div class="h-8 w-8 rounded-full bg-gray-600 flex items-center justify-center">
                        <span class="text-white text-sm font-medium">
                            {{ mb_substr(Auth::user()->name ?? 'Г', 0, 1) }}
                        </span>
                </div>
            </div>
        </div>
    </div>
</nav>

<main class="py-6">
    @yield('content')
</main>

<footer class="bg-gray-900 text-white py-4 mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-400">
        &copy; {{ date('Y') }} Мой сайт. Все права защищены.
    </div>
</footer>
</body>
</html>

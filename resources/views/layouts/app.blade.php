<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" href="{{asset('icon2.png')}}" type="image/x-icon">

    <!-- Скрипт установки темы ДО отрисовки, чтобы не было "мигания" -->
    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = stored || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Vite (SCSS + JS) -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')

    <style>
        /* Плавный переход при смене темы */
        html, body, .navbar, .alert, footer {
            transition: background-color .25s ease, color .25s ease, border-color .25s ease;
        }
        /* Небольшая доводка: убираем резкий белый цвет у навбара/футера */
        [data-bs-theme="dark"] .navbar,
        [data-bs-theme="dark"] footer {
            background-color: var(--bs-body-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
        /* Кнопка темы */
        #theme-toggle {
            border: none;
            background: transparent;
            color: var(--bs-body-color);
            font-size: 1.15rem;
            padding: .25rem .5rem;
            cursor: pointer;
        }
        #theme-toggle:hover { opacity: .75; }
    </style>
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-body shadow-sm border-bottom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                {{ config('app.name') }}
            </a>

            <div class="d-flex align-items-center order-md-last">
                <!-- Кнопка переключения темы -->
                <button id="theme-toggle" type="button" aria-label="Переключить тему" title="Переключить тему">
                    <i id="theme-toggle-icon" class="bi bi-moon-stars"></i>
                </button>

                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Левая часть: ссылки для авторизованных -->
                <ul class="navbar-nav me-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                               href="{{ route('dashboard') }}">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('advertisements.*') ? 'active' : '' }}"
                               href="{{ route('advertisements.index') }}">Объявления</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}"
                               href="{{ route('news.index') }}">Новости</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}"
                               href="{{ route('reviews.index') }}">Отзывы</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                               href="{{ route('users.index') }}">Пользователи</a>
                        </li>
                    @endauth
                </ul>

                <!-- Правая часть: аутентификация -->
                <ul class="navbar-nav ms-auto align-items-md-center">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item fw-bold">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Войти') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item fw-bold">
                            <a class="nav-link" href="{{route('users.show', Auth::user())}}">
                                {{ Auth::user()->name }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Выйти из профиля') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            <!-- Flash сообщения -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Футер -->
    <footer class="bg-body border-top py-3 mt-4">
        <div class="container text-center text-muted small">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Для частного использования.
        </div>
    </footer>
</div>

<!-- Логика переключения темы -->
<script>
    (function () {
        const root = document.documentElement;
        const btn = document.getElementById('theme-toggle');
        const icon = document.getElementById('theme-toggle-icon');

        function updateIcon(theme) {
            if (!icon) return;
            icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
        }

        // Инициализация иконки под текущую тему
        updateIcon(root.getAttribute('data-bs-theme') || 'light');

        btn && btn.addEventListener('click', function () {
            const current = root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            updateIcon(next);
        });

        // Следим за системной темой, если пользователь не выбрал вручную
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                const next = e.matches ? 'dark' : 'light';
                root.setAttribute('data-bs-theme', next);
                updateIcon(next);
            }
        });
    })();
</script>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

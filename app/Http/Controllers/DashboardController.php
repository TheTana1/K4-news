<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\News;
use App\Models\Review;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private const CACHE_TTL = 900; // 15 минут

    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        $userId = auth()->id();

        $stats = Cache::tags(['dashboard'])->remember("dashboard:stats:{$userId}", self::CACHE_TTL, fn () => [
            'ads_count'     => Advertisement::forCurrentUser()->count(),
            'news_count'    => News::forCurrentUser()->count(),
            'reviews_count' => Review::count(),
            'users_count'   => User::where('telegram_username', '!=', 'admin')->count(),
        ]);

        $recentAds = Cache::tags(['dashboard'])->remember("dashboard:ads:{$userId}", self::CACHE_TTL,
            fn () => Advertisement::forCurrentUser()->latest()->take(5)->get());

        $recentNews = Cache::tags(['dashboard'])->remember("dashboard:news:{$userId}", self::CACHE_TTL,
            fn () => News::forCurrentUser()->latest()->take(5)->get());

        $recentReviews = Cache::tags(['dashboard'])->remember('dashboard:reviews:global', self::CACHE_TTL,
            fn () => Review::latest()->take(5)->get());

        // --- Календарь смен ---
        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();

        // Существующие смены пользователя за текущий месяц
        $shifts = Shift::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn ($s) => $s->date->format('Y-m-d'));

        $days = [];
        $current = $startOfMonth->copy();

        while ($current <= $endOfMonth) {
            $key   = $current->format('Y-m-d');
            $shift = $shifts->get($key);

            $days[] = [
                'date'       => $key,
                'day'        => $current->day,
                'weekday'    => $current->isoFormat('dd'),
                'is_weekend' => $current->isWeekend(),
                'type'       => $shift?->type   ?? 'off',
                'status'     => $shift?->status ?? 'draft',
            ];

            $current->addDay();
        }

        return view('dashboard', compact(
            'stats', 'days',
            'recentAds', 'recentNews', 'recentReviews'
        ));
    }

    public function storeShifts(Request $request)
    {
        $validated = $request->validate([
            'days'        => 'required|array',
            'days.*.date' => 'required|date',
            'days.*.type' => 'required|in:off,full,half',
        ]);

        $userId = auth()->id();

        foreach ($validated['days'] as $day) {
            Shift::updateOrCreate(
                ['user_id' => $userId, 'date' => $day['date']],
                [
                    'type'   => $day['type'],
                    'status' => $day['type'] === 'off' ? 'draft' : 'pending',
                ]
            );
        }

        // Сбрасываем кэш статистики, если он зависит от смен
        Cache::tags(['dashboard'])->flush();

        return response()->json(['success' => true]);
    }
}

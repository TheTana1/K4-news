<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const CACHE_TTL = 300; // 5 минут

    public function index(Request $request): View
    {
        $userId = auth()->id();

        // Статистика
        $stats = Cache::tags(['dashboard'])->remember(
            "dashboard:stats:{$userId}",
            self::CACHE_TTL,
            function () {
                return [
                    'ads_count'     => \App\Models\Advertisement::count(),
                    'news_count'    => \App\Models\News::count(),
                    'reviews_count' => \App\Models\Review::count(),
                    'users_count'   => \App\Models\User::count(),
                ];
            }
        );

        // Последние записи
        $recentAds = Cache::tags(['dashboard'])->remember(
            "dashboard:recent-ads:{$userId}",
            self::CACHE_TTL,
            fn () => \App\Models\Advertisement::latest()->take(5)->get()
        );

        $recentNews = Cache::tags(['dashboard'])->remember(
            "dashboard:recent-news:{$userId}",
            self::CACHE_TTL,
            fn () => \App\Models\News::latest()->take(5)->get()
        );

        $recentReviews = Cache::tags(['dashboard'])->remember(
            "dashboard:recent-reviews:{$userId}",
            self::CACHE_TTL,
            fn () => \App\Models\Review::latest()->take(5)->get()
        );

        // Календарь смен
        $days = Cache::tags(['dashboard'])->remember(
            "dashboard:shifts:{$userId}",
            self::CACHE_TTL,
            function () use ($userId) {
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfMonth();

                // Все смены за месяц — одним запросом
                $shifts = Shift::where('user_id', $userId)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->get()
                    ->keyBy(fn ($s) => $s->date->format('Y-m-d'));

                $days    = [];
                $current = $start->copy();

                while ($current <= $end) {
                    $dateKey = $current->format('Y-m-d');
                    $shift   = $shifts[$dateKey] ?? null;

                    $days[] = [
                        'date'       => $dateKey,
                        'day'        => $current->day,
                        'weekday'    => $current->isoFormat('dd'),
                        'is_weekend' => $current->isWeekend(),
                        'type'       => $shift?->type   ?? 'off',
                        'status'     => $shift?->status ?? 'draft',
                    ];

                    $current->addDay();
                }

                return $days;
            }
        );
        return view('dashboard', compact(
            'stats',
            'recentAds',
            'recentNews',
            'recentReviews',
            'days'
        ));
    }
}

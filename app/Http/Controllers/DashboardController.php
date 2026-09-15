<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\News;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private const CACHE_TTL = 900; //15минут
    public function index()
    {
        $userId = auth()->id(); //работает dashboard в отличие от $userId->id

        $stats = Cache::tags(['dashboard'])->remember("dashboard:stats:{$userId}", self::CACHE_TTL, fn () => [
            'ads_count'     => Advertisement::forCurrentUser()->count(),
            'news_count'    => News::forCurrentUser()->count(),
            'reviews_count' => Review::count(),
            'users_count'   => User::forCurrentUser()->count(),
        ]);

        $recentAds = Cache::tags(['dashboard'])->remember("dashboard:ads:{$userId}", self::CACHE_TTL,
            fn () => Advertisement::forCurrentUser()->latest()->take(5)->get());

        $recentNews = Cache::tags(['dashboard'])->remember("dashboard:news:{$userId}", self::CACHE_TTL,
            fn () => News::forCurrentUser()->latest()->take(5)->get());

        $recentReviews = Cache::tags(['dashboard'])->remember('dashboard:reviews:global', self::CACHE_TTL,
            fn () => Review::latest()->take(5)->get());

        return view('dashboard', compact('stats', 'recentAds', 'recentNews', 'recentReviews'));
    }
}

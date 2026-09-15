<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\News;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $cacheKey = "dashboard:user:{$user->id}";

        $data = Cache::tags(['dashboard', "dashboard:user:{$user->id}"])
            ->remember($cacheKey, 10, function () {
                return [
                    'stats' => [
                        'ads_count'     => Advertisement::forCurrentUser()->count(),
                        'news_count'    => News::forCurrentUser()->count(),
                        'reviews_count' => Review::count(),
                        'users_count'   => User::forCurrentUser()->count(),
                    ],
                    'recentAds'     => Advertisement::forCurrentUser()->latest()->take(5)->get(),
                    'recentNews'    => News::forCurrentUser()->latest()->take(5)->get(),
                    'recentReviews' => Review::latest()->take(5)->get(),
                ];
            });

        return view('dashboard', $data);
    }
}

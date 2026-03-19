<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\News;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'ads_count' => Advertisement::count(),
            'news_count' => News::count(),
            'reviews_count' => Review::count(),
            'users_count' => User::count(),
            'active_in_group' => User::where('is_active_in_group', true)->count(),
        ];

        $recentAds = Advertisement::latest()->take(5)->get();
        $recentNews = News::latest()->take(5)->get();
        $recentReviews = Review::with('user')->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentAds', 'recentNews', 'recentReviews'));
    }
}

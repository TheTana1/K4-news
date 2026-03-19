<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(readonly ReviewRepository $reviewRepository)
    {

    }
    public function index():View
    {
        $reviews = Review::query()->paginate(10)->withQueryString();
        return view('reviews.index', compact('reviews'));
    }

    public function create():View
    {
        return view('reviews.create');
    }

    public function edit(Review $review):View
    {
        return view('reviews.edit', compact('review'));
    }

    public function show(Review $review):View
    {
        return view('reviews.show', compact('review'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(readonly ReviewRepository $reviewRepository)
    {

    }
    public function index():View
    {
        $reviews = $this->reviewRepository->paginate();
        return view('reviews.index', compact('reviews'));
    }

//    public function create():View
//    {
//        return view('reviews.create');
//    }

//    public function edit(Review $review):View
//    {
//        return view('reviews.edit', compact('review'));
//    }

    public function show(Review $review):View
    {
        $data = $this->reviewRepository->show($review);
        return view('reviews.show',[
            'review'=>$data['review'],
            'comments'=>$data['comments'],
        ]);
    }

    public function destroy(Review $review):RedirectResponse
    {
        $result = $this->reviewRepository->delete($review);
        return $result ?
            redirect()->route('reviews.index')->with('success','Успешное удаление отзыва'):
            redirect()->route('reviews.index')->with('error','Ошибка удаления отзыва');
    }
}

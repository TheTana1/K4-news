<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Repositories\NewsRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(readonly NewsRepository $newsRepository)
    {

    }
    public function index():View
    {
        $news = News::latest()->paginate(10)->withQueryString();
        return view('news.index', compact('news'));
    }

    public function show(News $news):View
    {
        return view('news.show', compact('news'));
    }

    public function create():View
    {
        return view('news.create');
    }

    public function edit(News $news):View
    {
        return view('news.edit', compact('news'));
    }
}

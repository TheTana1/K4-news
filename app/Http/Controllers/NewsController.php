<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Http\Requests\NewsRequest;
use App\Repositories\NewsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(readonly NewsRepository $newsRepository)
    {

    }

    public function index(): View
    {
        $news = $this->newsRepository->paginate();
        return view('news.index', compact('news'));
    }

    public function create(): View
    {
        return view('news.create');
    }

    public function show(News $news): View
    {
        return view('news.show', compact('news'));
    }

    public function edit(News $news): View
    {
        return view('news.edit', compact('news'));
    }

    public function store(NewsRequest $request): RedirectResponse
    {
        $news = $this->newsRepository->store($request);

        return redirect()
            ->route('news.show', $news)
            ->with('success', 'Новость успешно создана');
    }

    public function update(NewsRequest $request, News $news): RedirectResponse
    {
        $updatedNews = $this->newsRepository->update($request, $news);

        return redirect()
            ->route('news.show', $updatedNews)
            ->with('success', 'Новость успешно обновлена');
    }

    public function destroy(News $news): RedirectResponse
    {
        $result = $this->newsRepository->destroy($news);

        return $result
            ? redirect()->route('news.index')->with('success', 'Новость успешно удалена')
            : redirect()->route('news.index')->with('error', 'Ошибка при удалении новости');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Http\Requests\NewsRequest;
use App\Repositories\NewsRepository;
use App\Services\NewsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(
        readonly NewsService $newsService,
        readonly NewsRepository $newsRepository)
    {
        $this->authorizeResource(News::class, 'news');
    }

    public function index(Request $request): View
    {
        $news = $this->newsRepository->index($request);
        return view('news.index', compact('news'));
    }

    public function create(): View
    {
        return view('news.create');
    }

    public function show(News $news): View
    {
        $data = $this->newsRepository->show($news);
        return view('news.show', [
            'news'=>$data['news'],
            'comments'=>$data['comments'],
        ]);
    }

    public function edit(News $news): View
    {
        return view('news.edit', compact('news'));
    }

    public function store(NewsRequest $request): RedirectResponse
    {
        $news = $this->newsRepository->store($request);

        if (!$news) {
            return back()
                ->withInput()
                ->with('error', 'Не удалось создать новость');
        }

        $this->newsService->sendNewsMessage($news,
            "❗ <b>Новая новость</b>"
        );

        return redirect()
            ->route('news.show', $news)
            ->with('success', 'Новость успешно создана');
    }

    public function update(NewsRequest $request, News $news): RedirectResponse
    {
        $news = $this->newsRepository->update($request, $news);

        if (!$news) {
            return back()
                ->withInput()
                ->with('error', 'Не удалось обновить новость');
        }

        $this->newsService->sendNewsMessage($news,
            "⚠ <b>Изменение новости</b>"
        );
        return redirect()
            ->route('news.show', $news)
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

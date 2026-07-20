<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdvertisementRequest;
use App\Models\Advertisement;
use App\Repositories\AdvertisementRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function __construct(readonly AdvertisementRepository $advertisementRepository)
    {
        // Проверка прав
        $this->authorizeResource(Advertisement::class, 'advertisement');
    }

    /**
     * Список всех объявлений
     */
    public function index(): View
    {
        $advertisements = $this->advertisementRepository->paginate();
        return view('advertisements.index', compact('advertisements'));
    }

    /**
     * Показать одно объявление
     */
    public function show(Advertisement $advertisement): View
    {
        $advertisement->load(['author', 'files', 'comments.user']);
        return view('advertisements.show', compact('advertisement'));
    }

    /**
     * Форма создания объявления
     */
    public function create(): View
    {
        return view('advertisements.create');
    }

    /**
     * Форма редактирования объявления
     */
    public function edit(Advertisement $advertisement): View
    {
        $advertisement->load('files');
        return view('advertisements.edit', compact('advertisement'));
    }

    /**
     * Сохранить новое объявление
     */
    public function store(AdvertisementRequest $request): RedirectResponse
    {
        $advertisement = $this->advertisementRepository->store($request);

        return redirect()
            ->route('advertisements.show', $advertisement)
            ->with('success', 'Объявление успешно создано');
    }

    /**
     * Обновить объявление
     */
    public function update(AdvertisementRequest $request, Advertisement $advertisement): RedirectResponse
    {
        $advertisement = $this->advertisementRepository->update($request, $advertisement);

        return redirect()
            ->route('advertisements.show', $advertisement)
            ->with('success', 'Объявление успешно обновлено');
    }

    /**
     * Удалить объявление
     */
    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        $result = $this->advertisementRepository->destroy($advertisement);

        return $result
            ? redirect()->route('advertisements.index')->with('success', 'Объявление успешно удалено')
            : redirect()->route('advertisements.index')->with('error', 'Ошибка при удалении объявления');
    }
}

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
        $this->authorizeResource(Advertisement::class, 'advertisement');
    }

    public function index(): View
    {
        $advertisements = $this->advertisementRepository->paginate();
        return view('advertisements.index', compact('advertisements'));
    }

    public function show(Advertisement $advertisement): View
    {
        $advertisement->load([ 'files', 'comments']);
        return view('advertisements.show', compact('advertisement'));
    }

    public function create(): View
    {
        return view('advertisements.create');
    }

    public function edit(Advertisement $advertisement): View
    {
        $advertisement->load('files');
        return view('advertisements.edit', compact('advertisement'));
    }

    public function store(AdvertisementRequest $request): RedirectResponse
    {
        $advertisement = $this->advertisementRepository->store($request);

        return redirect()
            ->route('advertisements.show', $advertisement)
            ->with('success', 'Объявление успешно создано');
    }

    public function update(AdvertisementRequest $request, Advertisement $advertisement): RedirectResponse
    {
        $advertisement = $this->advertisementRepository->update($request, $advertisement);

        return redirect()
            ->route('advertisements.show', $advertisement)
            ->with('success', 'Объявление успешно обновлено');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        $result = $this->advertisementRepository->destroy($advertisement);

        return $result
            ? redirect()->route('advertisements.index',with('role'))->with('success', 'Объявление успешно удалено')
            : redirect()->route('advertisements.index',with('role'))->with('error', 'Ошибка при удалении объявления');
    }
}

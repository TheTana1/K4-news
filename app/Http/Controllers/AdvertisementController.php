<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdvertisementRequest;
use App\Models\Advertisement;
use App\Repositories\AdvertisementRepository;
use App\Services\AdvertisementService;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function __construct(readonly AdvertisementService    $advertisementService,
                                readonly AdvertisementRepository $advertisementRepository)
    {
        $this->authorizeResource(Advertisement::class, 'advertisement');
    }

    public function index(Request $request): View
    {
        $advertisements = $this->advertisementRepository->index($request);
        return view('advertisements.index', compact('advertisements'));
    }

    public function show(Advertisement $advertisement): View
    {

        $data = $this->advertisementRepository->show($advertisement);
        return view('advertisements.show', [
            'advertisement' => $data['advertisement'],
            'comments' => $data['comments']
        ]);
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
        if (!$advertisement) {
            return back()
                ->withInput()
                ->with('error', 'Не удалось создать объявление');
        }
        $this->advertisementService->sendAdvertisementMessage($advertisement,
            "‼ <b>Новое объявление</b>"
        );

        return redirect()
            ->route('advertisements.show', $advertisement)
            ->with('success', 'Объявление успешно создано');
    }

    public function update(AdvertisementRequest $request, Advertisement $advertisement): RedirectResponse
    {
        $advertisement = $this->advertisementRepository->update($request, $advertisement);

        if (!$advertisement) {
            return back()
                ->withInput()
                ->with('error', 'Не удалось обновить объявление');
        }

        $this->advertisementService->sendAdvertisementMessage($advertisement,
            "⚠ <b>Изменение объявления</b>"
        );
        return redirect()
            ->route('advertisements.show', $advertisement)
            ->with('success', 'Объявление успешно обновлено');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        $result = $this->advertisementRepository->destroy($advertisement);

        return $result
            ? redirect()->route('advertisements.index', with('role'))->with('success', 'Объявление успешно удалено')
            : redirect()->route('advertisements.index', with('role'))->with('error', 'Ошибка при удалении объявления');
    }
}

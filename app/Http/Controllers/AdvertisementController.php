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

    }
    public function index(): View
    {
        $advertisements = Advertisement::latest()->paginate(10)->withQueryString();
        return view('advertisements.index', compact('advertisements'));
    }

    public function show(Advertisement $advertisement):View
    {
        return view('advertisements.show', compact('advertisement'));
    }

    public function create():View
    {
        return view('advertisements.create');
    }

    public function edit(Advertisement $advertisement): View
    {
        return view('advertisements.edit', compact('advertisement'));
    }

    public function store(AdvertisementRequest $request): RedirectResponse
    {
        return redirect()->route('advertisements.index', $this->advertisementRepository->store($request));
    }

    public function update(AdvertisementRequest $request, Advertisement $advertisement):RedirectResponse
    {
        return redirect()->route('advertisements.show', $this->advertisementRepository->update($request,$advertisement));
    }
}

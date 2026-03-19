<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
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
}

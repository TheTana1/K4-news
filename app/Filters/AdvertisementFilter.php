<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdvertisementFilter
{
    public static function apply(Request $request, Builder $query): Builder
    {
        if ($request->filled('content')) {
            $query->whereRaw('LOWER(content) LIKE ?', ['%' . mb_strtolower($request->input('content')) . '%']);
        }

        if ($request->filled('author')) {
            $author = mb_strtolower($request->input('author'));
            $query->whereHas('user', function ($q) use ($author) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $author . '%']);
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query;
    }
}

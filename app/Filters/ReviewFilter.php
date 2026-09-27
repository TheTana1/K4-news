<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewFilter
{

    public static function apply(Request $request, Builder $query): Builder
    {
        if ($request->filled('content')) {
            $query->whereRaw('LOWER(content) LIKE ?', ['%' . mb_strtolower($request->input('content')) . '%']);
        }
        if ($request->filled('author')) {
            $query->whereRaw('LOWER(telegram_author_name) LIKE ?', ['%' . mb_strtolower($request->input('author')) . '%']);
        }
        if ($request->filled('rating')) {
            $query->where('rating', $request->input('rating'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('published_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('published_at', '<=', $request->input('date_to'));
        }

        return $query;
    }
}

<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdvertisementFilter
{
    public static function apply(Request $request, Builder $query): Builder
    {
        if ($request->has('content') && $request->input('content') != null) {
            $query->whereRaw('LOWER(content) LIKE ?', ['%' . strtolower($request->input('content')) . '%']);
        }
        if ($request->has('author') && $request->input('author') != null) {
            $author = mb_strtolower($request->input('author'));
            $query->whereHas('user', function ($q) use ($author) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $author . '%']);
            });
        }
        if ($request->has('role_id') && $request->input('role_id') != null) {
            $query->where('role_id',  $request->input('role_id'));
        }
        if ($request->has('date_from') && $request->input('date_from') != null) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to') && $request->input('date_to') != null) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        if ($request->has('status') && $request->input('status') != null) {
            $query->where('status',  $request->input('status'));
        }

        return $query;
    }
}

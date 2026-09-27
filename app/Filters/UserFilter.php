<?php

namespace App\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserFilter
{
    public static function apply(Request $request, Builder $query): Builder
    {
        if ($request->filled('name')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($request->input('name')) . '%']);
        }
            if ($request->filled('email')) {
            $query->whereRaw('LOWER(email) LIKE ?', ['%' . mb_strtolower($request->input('email')) . '%']);
        }
        if ($request->filled('username')) {
            $username = mb_strtolower(ltrim($request->input('username'), '@'));
            $query->whereRaw('LOWER(telegram_username) LIKE ?', ['%' . $username . '%']);
        }
        if ($request->filled('role_id')) {
            $query->where('role_id',  $request->input('role_id'));
        }
        if ($request->filled('is_active_in_group')) {
            $query->where('is_active_in_group', $request->input('is_active_in_group'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        return $query;
    }
}

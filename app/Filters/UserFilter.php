<?php

namespace App\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserFilter
{
    public static function apply(Request $request, Builder $query): Builder
    {
        if ($request->has('name') && $request->input('name') != null) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
        }
        if ($request->has('email') && $request->input('email') != null) {
            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($request->input('email')) . '%']);
        }
        if ($request->has('username') && $request->input('username') != null) {
            $username = strtolower(ltrim($request->input('username'), '@'));
            $query->whereRaw('LOWER(telegram_username) LIKE ?', ['%' . $username . '%']);
        }
        if ($request->has('role_id') && $request->input('role_id') != null) {
            $query->where('role_id',  $request->input('role_id'));
        }
        if ($request->has('is_active_in_group') && $request->input('is_active_in_group') != null) {
            $query->where('is_active_in_group', $request->input('is_active_in_group'));
        }
        if ($request->has('date_from') && $request->input('date_from') != null) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to') && $request->input('date_to') != null) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        return $query;
    }
}

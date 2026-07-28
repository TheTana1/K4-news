<?php

namespace App\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserFilter
{
    public static function apply(Request $request, Builder $query): Builder
    {
        if ($request->has('name') && $request->input('name') != null) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->has('email') && $request->input('email') != null) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->has('username') && $request->input('username') != null) {
            $query->where('username', 'like', '%' . $request->input('username') . '%');
        }
        if ($request->has('role_id') && $request->input('role_id') != null) {
            $query->where('role_id',  $request->input('role_id'));
        }
        if ($request->has('is_active_in_group') && $request->input('is_active_in_group') != null) {
            $query->where('is_active_in_group', $request->input('is_active_in_group'));
        }

        return $query;
    }
}

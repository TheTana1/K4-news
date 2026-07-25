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
        switch ($request->input('sort')) {
            case 'oldest':
                $query->orderBy('created_at');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'email_desc':
                $query->orderBy('email', 'desc');
                break;
            case 'email_asc':
                $query->orderBy('email');
                break;
            case 'role_desc':
                $query->orderBy('role', 'desc');
                break;
            case 'role_asc':
                $query->orderBy('role');
                break;
            case 'gender_desc':
                $query->orderBy('gender', 'desc');
                break;
            case 'gender_asc':
                $query->orderBy('gender');
                break;
        }
        return $query;
    }
}

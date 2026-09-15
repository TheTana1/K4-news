<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minDate = Carbon::today()->subYears(95)->format('Y-m-d');
        $maxDate = Carbon::today()->subYears(15)->format('Y-m-d');
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:2|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'birthday' => 'nullable|date|before:' . $maxDate . '|after:' . $minDate,
            'gender' => 'nullable|in:0,1',
            'role_id' => 'nullable|exists:roles,id',
            'telegram_id' => 'nullable|string|max:255|unique:users,telegram_id',
            'telegram_username' => 'nullable|string|max:255',
            'is_active_in_group' => 'nullable|boolean',
            'phone'              => 'required|string|max:20',
        ];
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'telegram_username' => 'required|string',
            'password'          => 'required|string',
            'cf-turnstile-response' => ['required', 'string', 'min:10', new \App\Rules\Turnstile()],
        ], [
            'cf-turnstile-response.required' => 'Подтвердите, что вы не робот.',
            'cf-turnstile-response.min'      => 'Токен капчи недействителен.',
        ]);
    }
    protected function attemptLogin(Request $request)
    {
        $username = strtolower(ltrim((string) $request->input('telegram_username'), '@'));
        $password = $request->input('password');

        $user = User::whereRaw('LOWER(telegram_username) = ?', [$username])->first();

        if ($user && Hash::check($password, $user->password)) {
            $this->guard()->login($user, $request->filled('remember'));
            return true;
        }

        return false;
    }

    protected function username()
    {
        return 'telegram_username';
    }

}

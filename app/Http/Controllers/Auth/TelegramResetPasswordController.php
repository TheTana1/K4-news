<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramResetPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
    public function send(Request $request, TelegramBotService $telegram)
    {
        $request->validate(['email' => 'required|email|max:255|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->telegram_id) {
            return back()->withErrors(['email' => 'Пользователь не найден или Telegram не привязан.']);
        }
        if (!$user->is_active_in_group){
            return back()->withErrors(['email' => 'Пользователь не активен, обратитесь к руководству']);
        }

        $newPassword = Str::random(12);

        $user->password = Hash::make($newPassword);
        $user->save();

        $text = "🔐 <b>Новый пароль для входа</b>\n\n";
        $text .= "Ваш новый пароль: <code>{$newPassword}</code>\n\n";
        $text .= "Рекомендуем сменить его после входа.";

        $sent = $telegram->sendHtmlMessage($user->telegram_id, $text);

        if (!$sent) {
            return back()->withErrors(['email' => 'Не удалось отправить сообщение в Telegram.']);
        }

        return back()->with('status ', 'Новый пароль отправлен в Telegram!');
    }
}

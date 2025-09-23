<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotController extends Controller
{
      public function showForgotForm()
    {
        return view('template.forgotForm');
    }
     public function sendResetLinkEmail(Request $request)
    {
        // Validasi email yang dimasukkan
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Kirim link reset password
        $response = Password::sendResetLink(
            $request->only('email')
        );

        // Cek apakah link reset berhasil dikirim
        if ($response == Password::RESET_LINK_SENT) {
            return back()->with('status', 'Kami telah mengirimkan link reset password ke email Anda.');
        }

        // Jika gagal, kembalikan dengan error
        return back()->withErrors(['email' => 'Gagal mengirim link reset password. Silakan coba lagi.']);
    }

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
}

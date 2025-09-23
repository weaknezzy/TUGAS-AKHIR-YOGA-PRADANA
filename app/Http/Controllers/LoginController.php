<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\keranjang;

class LoginController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm()
    {
        // Jika user sudah login, redirect sesuai role
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        // Inisialisasi keranjang kosong untuk navbar
        $keranjangItems = collect();
        
        return view('template.loginForm', compact('keranjangItems'));
    }

    // Proses login
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        // Cek apakah email terdaftar
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak terdaftar dalam sistem kami.',
            ])->withInput($request->only('email', 'remember'));
        }

        // Proses autentikasi
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            $user = Auth::user();
            
            // Tambahkan pesan selamat datang yang lebih informatif
            $request->session()->flash('success', 'Selamat datang kembali, ' . $user->name . '! 🎉');
            $request->session()->flash('login_success', true);
            
            return $this->redirectBasedOnRole($user);
        }

        // Jika password salah
        return back()->withErrors([
            'password' => 'Password yang Anda masukkan salah.',
        ])->withInput($request->only('email', 'remember'));
    }

    // Redirect berdasarkan role
    protected function redirectBasedOnRole($user)
    {
        switch ($user->role) {
            case 'admin':
            case 'owner':
                return redirect()->route('filament.admin.pages.dashboard');
            case 'pelanggan':
                return redirect()->route('home');
            default:
                return redirect('/');
        }
    }

    // Logout pengguna
    public function logout(Request $request)
    {
        // Simpan role sebelum logout
        $userRole = Auth::user()->role;
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect berdasarkan role sebelumnya
        if (in_array($userRole, ['admin', 'owner'])) {
            return redirect()->route('home');
        }
        
        return redirect()->route('login');
    }
}

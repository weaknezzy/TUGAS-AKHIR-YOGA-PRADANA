<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\keranjang;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    // Menampilkan form registrasi
    public function showRegisterForm()
    {
        // Jika sudah login, redirect sesuai role
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $keranjangItems = collect();
        return view('template.registerForm', compact('keranjangItems'));
    }

    // Proses registrasi pengguna baru
    public function register(Request $request)
    {
        // Validasi input form
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'no_telp' => 'required|string|min:10|max:15',
            'address' => 'required|string|min:10',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama harus diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'no_telp.required' => 'Nomor telepon harus diisi',
            'no_telp.min' => 'Nomor telepon minimal 10 digit',
            'no_telp.max' => 'Nomor telepon maksimal 15 digit',
            'address.required' => 'Alamat harus diisi',
            'address.min' => 'Alamat terlalu pendek, minimal 10 karakter',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        try {
            // Menyimpan data user ke dalam tabel users
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'no_telp' => $validated['no_telp'],
                'alamat' => $validated['address'], // Perhatikan: kolom di database adalah 'alamat'
                'password' => Hash::make($validated['password']),
                'role' => 'pelanggan',
            ]);

            // Login otomatis setelah registrasi
            Auth::login($user);

            // Redirect ke halaman home dengan pesan selamat datang
            return redirect()->route('home')
                ->with('success', 'Selamat datang di Warung Makan Rumah Pakde Along! Akun Anda berhasil dibuat.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Registration error: ' . $e->getMessage());
            
            // Redirect kembali dengan pesan error umum
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.']);
        }
    }

    // Validasi email real-time
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        
        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format email tidak valid'
            ]);
        }
        
        // Cek apakah email sudah terdaftar
        $userExists = User::where('email', $email)->exists();
        
        if ($userExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email sudah terdaftar dan digunakan'
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Email tersedia'
        ]);
    }

    // Redirect berdasarkan role
    protected function redirectBasedOnRole($user)
    {
        switch ($user->role) {
            case 'admin':
            case 'owner':
                return redirect()->route('filament.admin.pages.dashboard');
            default:
                return redirect()->route('home');
        }
    }
}

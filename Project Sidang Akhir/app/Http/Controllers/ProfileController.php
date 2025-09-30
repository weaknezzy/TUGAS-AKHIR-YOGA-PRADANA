<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pemesanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Keranjang; // Added this import for Keranjang model

class ProfileController extends Controller
{
    public function showHome(Request $request)
    {
        // Handle POST request untuk menghapus session login_success
        if ($request->isMethod('post') && $request->has('clear_login_success')) {
            $request->session()->forget('login_success');
            return response()->json(['success' => true]);
        }

        return view('home', [
            'welcomeMessage' => session('success', '')
        ]);
    }

    public function showProfile(Request $request)
    {
        // Handle POST request untuk menghapus nomor HP
        if ($request->isMethod('post') && $request->has('action') && $request->action === 'clear_phone') {
            $request->session()->forget(['guest_phone', 'guest_name']);
            // Redirect ke halaman profile tanpa parameter
            return redirect()->route('user.profile');
        }

        $isGuest = !Auth::check();
        $user = [];
        $orders = collect();
        $searchError = null;
        $searchPerformed = false;
        $isFromOrder = false; // Flag untuk menandai apakah akses dari pemesanan
        $hasNoTelp = $request->has('no_telp'); // Variabel untuk template

        if ($isGuest) {
            // Jika guest sudah mencari dengan nomor HP sebelumnya
            $guestPhone = $request->no_telp ?? session('guest_phone');
            $guestName = session('guest_name');
            
            // Cek apakah ini akses dari pemesanan (ada session order_success)
            if (session('order_success') && session('guest_phone')) {
                $isFromOrder = true;
                $guestPhone = session('guest_phone');
                $guestName = session('guest_name');
                // Hapus session order_success agar tidak muncul lagi
                session()->forget('order_success');
                $hasNoTelp = false; // Tidak ada parameter no_telp saat dari pemesanan
            }
            
            // Jika ada parameter no_telp baru, ini adalah pencarian manual
            if ($request->has('no_telp')) {
                // Jika nomor HP berbeda dengan yang di session, ini pencarian baru
                if ($request->no_telp !== session('guest_phone')) {
                    session()->forget(['guest_phone', 'guest_name']);
                    $guestPhone = $request->no_telp;
                    $isFromOrder = false;
                    $searchPerformed = true; // Ini adalah pencarian manual
                    $hasNoTelp = true; // Ada parameter no_telp
                } else {
                    // Jika nomor HP sama dengan session, ini refresh halaman
                    $isFromOrder = false;
                    $searchPerformed = false;
                    $hasNoTelp = true; // Ada parameter no_telp
                }
            } else {
                // Tidak ada parameter no_telp, cek apakah ada session
                if (session('guest_phone')) {
                    // Ada session, tapi tidak ada order_success, ini akses langsung atau refresh
                    $isFromOrder = false;
                    $searchPerformed = false;
                    $hasNoTelp = false; // Tidak ada parameter no_telp
                }
            }
            
            if ($guestPhone) {
                // Validasi format nomor HP - mendukung nomor HP Indonesia yang lebih panjang
                if (!preg_match('/^(\+62|62|0)8[1-9][0-9]{6,11}$/', $guestPhone)) {
                    $searchError = "Format nomor HP tidak valid. Silakan masukkan nomor HP yang benar.";
                    $user = [
                        'no_telp' => $guestPhone,
                        'name' => 'Guest',
                        'role' => 'guest'
                    ];
                    // Jangan simpan nomor HP ke session jika format tidak valid
                    session()->forget(['guest_phone', 'guest_name']);
                } else {
                    $orders = Pemesanan::where('no_telp', $guestPhone)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($order) {
                            return [
                                'order_id' => $order->order_id, // gunakan order_id string
                                'status' => $order->status,
                                'date' => $order->created_at->format('d M Y H:i'),
                                'customer_name' => $order->customer_name,
                                'no_telp' => $order->no_telp
                            ];
                        });

                    if ($orders->count() > 0) {
                        // Simpan nomor HP ke session jika ditemukan pesanan
                        session(['guest_phone' => $guestPhone]);
                        $user = [
                            'no_telp' => $guestPhone,
                            'name' => $guestName ?? $orders->first()['customer_name'],
                            'role' => 'guest'
                        ];
                    } else {
                        // Jika tidak ada pesanan ditemukan
                        $searchError = "Tidak ada pesanan ditemukan untuk nomor HP: {$guestPhone}";
                        $user = [
                            'no_telp' => $guestPhone,
                            'name' => 'Guest',
                            'role' => 'guest'
                        ];
                        // Jangan simpan nomor HP ke session jika tidak ada pesanan
                        session()->forget(['guest_phone', 'guest_name']);
                    }
                }
            }
        } else {
            $authUser = Auth::user();
            $orders = Pemesanan::where('user_id', $authUser->id_user)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($order) {
                    return [
                        'order_id' => $order->order_id, // gunakan order_id string
                        'status' => $order->status,
                        'date' => $order->created_at->format('d M Y H:i'),
                        'customer_name' => $order->customer_name,
                        'no_telp' => $order->no_telp
                    ];
                });

            $user = [
                'name' => $authUser->name,
                'email' => $authUser->email,
                'no_telp' => $authUser->no_telp,
                'alamat' => $authUser->alamat,
                'role' => $authUser->role
            ];
        }

        // Ambil keranjang items untuk navbar
        if (Auth::check()) {
            $keranjangItems = Keranjang::with('menu')
                ->where('id_user', Auth::user()->id_user)
                ->get();
        } else {
            $keranjangItems = Keranjang::with('menu')
                ->where('session_id', session()->getId())
                ->whereNull('id_user')
                ->get();
        }

        // Tambahkan perhitungan total harga keranjang
        $total = $keranjangItems->sum(function($item) {
            return $item->menu->harga * $item->jumlah;
        });

        return view('template.profileform', compact('user', 'orders', 'isGuest', 'keranjangItems', 'total', 'searchError', 'searchPerformed', 'isFromOrder', 'hasNoTelp'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::user()->id_user . ',id_user',
            'no_telp' => 'required|string|min:10|max:15',
            'alamat' => 'required|string|min:10',
        ], [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'no_telp.required' => 'Nomor telepon harus diisi',
            'no_telp.min' => 'Nomor telepon minimal 10 digit',
            'no_telp.max' => 'Nomor telepon maksimal 15 digit',
            'alamat.required' => 'Alamat harus diisi',
            'alamat.min' => 'Alamat terlalu pendek, minimal 10 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Update user data menggunakan DB query builder
            DB::table('users')
                ->where('id_user', $user->id_user)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'no_telp' => $request->no_telp,
                    'alamat' => $request->alamat,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui profil'
            ], 500);
        }
    }
}

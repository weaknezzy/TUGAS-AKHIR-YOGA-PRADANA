<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\keranjang;

class ShareKeranjangData
{
    public function handle(Request $request, Closure $next)
    {
        // Inisialisasi keranjang kosong
        $keranjangItems = collect();
        $total = 0;

        // Jika user login, ambil data keranjang
        if (Auth::check()) {
            $keranjangItems = keranjang::with('menu')->where('id_user', Auth::id())->get();
            
            // Hitung total
            $total = $keranjangItems->sum(function($item) {
                return $item->menu->harga * $item->jumlah;
            });
        }

        // Share data ke semua view
        View::share('keranjangItems', $keranjangItems);
        View::share('total', $total);

        return $next($request);
    }
} 
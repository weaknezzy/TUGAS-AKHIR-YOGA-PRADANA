<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\keranjang;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;

class MainMenuController extends Controller
{
    public function ShowMenuMakanan()
    {
        $menus = Menu::where('kategori', 'Makanan')->get();

        // Ambil keranjang items berdasarkan user status
        if (Auth::check()) {
            $keranjangItems = keranjang::with('menu')
                ->where('id_user', Auth::user()->id_user)
                ->get();
        } else {
            $keranjangItems = keranjang::with('menu')
                ->where('session_id', session()->getId())
                ->whereNull('id_user')
                ->get();
        }

        // Hitung total harga keranjang
        $total = $keranjangItems->sum(fn($item) => $item->menu->harga * $item->jumlah);

        // Hitung jumlah item keranjang
        $cartCount = $keranjangItems->sum('jumlah');

        // Kirim data ke blade
        return view('template.menuMakanan', compact('menus', 'total', 'cartCount', 'keranjangItems'));
    }

    public function ShowMenuMinuman()
    {
        $menus = Menu::where('kategori', 'Minuman')->get();
        
        // Ambil keranjang items berdasarkan user status
        if (Auth::check()) {
            $keranjangItems = keranjang::with('menu')
                ->where('id_user', Auth::user()->id_user)
                ->get();
        } else {
            $keranjangItems = keranjang::with('menu')
                ->where('session_id', session()->getId())
                ->whereNull('id_user')
                ->get();
        }

        // Hitung total harga keranjang
        $total = $keranjangItems->sum(fn($item) => $item->menu->harga * $item->jumlah);

        // Hitung jumlah item keranjang
        $cartCount = $keranjangItems->sum('jumlah');

        // Kirim data ke blade
        return view('template.menuMinuman', compact('menus', 'total', 'cartCount', 'keranjangItems'));
    }

    public function ShowMenuCatering()
    {
        // Ambil keranjang items berdasarkan user status
        if (Auth::check()) {
            $keranjangItems = keranjang::with('menu')
                ->where('id_user', Auth::user()->id_user)
                ->get();
        } else {
            $keranjangItems = keranjang::with('menu')
                ->where('session_id', session()->getId())
                ->whereNull('id_user')
                ->get();
        }

        // Hitung total harga keranjang
        $total = $keranjangItems->sum(fn($item) => $item->menu->harga * $item->jumlah);

        // Hitung jumlah item keranjang
        $cartCount = $keranjangItems->sum('jumlah');

        // Kirim data ke blade
        return view('template.menuCatering', compact('total', 'cartCount', 'keranjangItems'));
    }

    public function showMenuDetail($id)
    {
        $menu = Menu::findOrFail($id);
        
        // Ambil keranjang items berdasarkan user status
        if (Auth::check()) {
            $keranjangItems = keranjang::with('menu')
                ->where('id_user', Auth::user()->id_user)
                ->get();
        } else {
            $keranjangItems = keranjang::with('menu')
                ->where('session_id', session()->getId())
                ->whereNull('id_user')
                ->get();
        }

        // Hitung total harga keranjang
        $total = $keranjangItems->sum(fn($item) => $item->menu->harga * $item->jumlah);

        // Hitung jumlah item keranjang
        $cartCount = $keranjangItems->sum('jumlah');

        return view('template.menuDetail', compact('menu', 'total', 'cartCount', 'keranjangItems'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Keranjang;
use App\Models\Menu;

class KeranjangController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $items = Keranjang::with('menu')
                ->where('id_user', Auth::user()->id_user)
                ->get();
            
            // Debugging
            dd([
                'user_id' => Auth::user()->id_user,
                'items' => $items->toArray(),
                'items_count' => $items->count(),
                'auth_check' => Auth::check()
            ]);
        } else {
            $items = Keranjang::with('menu')
                ->where('session_id', session()->getId())
                ->whereNull('id_user')
                ->get();
        }

        $total = $items->sum(function ($item) {
            return $item->menu->harga * $item->jumlah;
        });

        return view('template.keranjang', [
            'keranjangItems' => $items,
            'total' => $total
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menu,id',
            'jumlah' => 'required|integer|min:1',
        ]);

        if (Auth::check()) {
            // Cek apakah item sudah ada untuk user ini
            $existingItem = Keranjang::where('id_user', Auth::user()->id_user)
                ->where('menu_id', $request->menu_id)
                ->first();

            if ($existingItem) {
                // Update jumlah jika sudah ada
                $existingItem->increment('jumlah', $request->jumlah);
            } else {
                // Buat item baru untuk user login
                Keranjang::create([
                    'id_user' => Auth::user()->id_user,
                    'menu_id' => $request->menu_id,
                    'jumlah' => $request->jumlah,
                    'session_id' => null
                ]);
            }
        } else {
            // Cek apakah item sudah ada untuk guest
            $existingItem = Keranjang::where('session_id', session()->getId())
                ->where('menu_id', $request->menu_id)
                ->whereNull('id_user')
                ->first();

            if ($existingItem) {
                // Update jumlah jika sudah ada
                $existingItem->increment('jumlah', $request->jumlah);
            } else {
                // Buat item baru untuk guest
                Keranjang::create([
                    'id_user' => null,
                    'menu_id' => $request->menu_id,
                    'jumlah' => $request->jumlah,
                    'session_id' => session()->getId()
                ]);
            }
        }

        return back()->with('success', 'Berhasil menambahkan ke keranjang.');
    }

    public function remove($id)
    {
        $query = Keranjang::where('id', $id);

        if (Auth::check()) {
            $query->where('id_user', Auth::user()->id_user);
        } else {
            $query->where('session_id', session()->getId());
        }

        $query->delete();

        return back()->with('success', 'Berhasil menghapus dari keranjang.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        $keranjang = Keranjang::where('id', $id);

        if (Auth::check()) {
            $keranjang->where('id_user', Auth::user()->id_user);
        } else {
            $keranjang->where('session_id', session()->getId());
        }

        $keranjang->update([
            'jumlah' => $request->jumlah,
        ]);

        return back()->with('success', 'Jumlah berhasil diperbarui.');
    }
}

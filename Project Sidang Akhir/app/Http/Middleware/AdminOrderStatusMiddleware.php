<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrderStatusMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user adalah admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'error' => 'Unauthorized. Hanya admin yang dapat mengubah status pesanan.'
            ], 403);
        }

        return $next($request);
    }
} 
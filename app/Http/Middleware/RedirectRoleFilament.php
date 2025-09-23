<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectRoleFilament
{
    public function handle(Request $request, Closure $next)
    {
        // If it's a logout request, let it pass through and handle redirect after
        if ($request->is('admin/logout')) {
            $response = $next($request);
            return $response->isRedirect() ? redirect('/') : $response;
        }

        $user = Auth::user();
        
        // If no user is logged in and trying to access admin
        if (!$user && str_starts_with($request->path(), 'admin')) {
            return redirect()->route('filament.admin.auth.login');
        }

        // If user is logged in
        if ($user) {
            $path = $request->path();

            // Jika mencoba mengakses panel admin (Filament)
            if (str_starts_with($path, 'admin')) {
                if (!in_array($user->role, ['admin', 'owner'])) {
                    return redirect()->route('home')
                        ->with('error', 'Maaf, Anda tidak memiliki izin untuk mengakses halaman admin.');
                }
            }
            // Jika mencoba mengakses halaman frontend
            else {
                if (in_array($user->role, ['admin', 'owner'])) {
                    return redirect()->route('filament.admin.pages.dashboard')
                        ->with('error', 'Silakan menggunakan panel admin untuk mengelola sistem.');
                }
            }
        }

        return $next($request);
    }
}

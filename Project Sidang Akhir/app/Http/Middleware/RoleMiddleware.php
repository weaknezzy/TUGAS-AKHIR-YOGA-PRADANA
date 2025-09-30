<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Jika user tidak login, izinkan akses ke halaman blade
        if (!Auth::check() && $request->is('*filament*')) {
            return redirect('/login');
        }

        // Jika user login
        if (Auth::check()) {
            $userRole = Auth::user()->role;

            // Jika admin/owner mencoba akses halaman blade
            if (($userRole === 'admin' || $userRole === 'owner') && !$request->is('*filament*')) {
                return redirect('/admin');
            }

            // Jika pelanggan mencoba akses filament
            if ($userRole === 'pelanggan' && $request->is('*filament*')) {
                return redirect('/');
            }

            // Jika role sesuai dengan yang diizinkan
            if (in_array($userRole, $roles)) {
                return $next($request);
            }
        }

        return $next($request);
    }
} 
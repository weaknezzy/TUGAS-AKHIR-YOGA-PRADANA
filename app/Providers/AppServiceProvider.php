<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Listen for logout events
        Event::listen(Logout::class, function (Logout $event) {
            if (in_array($event->user->role, ['admin', 'owner'])) {
                return redirect('/');
            }
        });

        // Share cart data with all views
        View::composer('*', function ($view) {
            $cartCount = 0;
            $cartItems = [];
            
            if (Auth::check()) {
                $user = Auth::user();
                $cartItems = \App\Models\keranjang::where('id_user', $user->id)->get();
                $cartCount = $cartItems->count();
            } else {
                $sessionId = session()->getId();
                $cartItems = \App\Models\keranjang::where('session_id', $sessionId)->get();
                $cartCount = $cartItems->count();
            }
            
            $view->with('cartCount', $cartCount);
            $view->with('cartItems', $cartItems);
        });
    }
}

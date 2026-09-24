<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Listener auth (login/logout/failed) terdaftar otomatis lewat
        // event discovery pada app/Listeners.
    }
}

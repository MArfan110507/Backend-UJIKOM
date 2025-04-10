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
        // Midtrans Config
        \Config::set('midtrans.serverKey', env('MIDTRANS_SERVER_KEY'));
        \Config::set('midtrans.isProduction', false); // Ubah ke true saat production
        \Config::set('midtrans.sanitized', true);
        \Config::set('midtrans.enable3ds', true);
    }
}

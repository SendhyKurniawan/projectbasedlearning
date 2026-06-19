<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

// Service provider utama aplikasi: tempat registrasi & bootstrap layanan global.
class AppServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan service ke container (belum dipakai).
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap layanan aplikasi saat aplikasi mulai.
     */
    public function boot(): void
    {
        // Munculkan error saat ada lazy loading (N+1) di lingkungan lokal/CI, bukan produksi.
        Model::preventLazyLoading(!app()->isProduction());

        // Daftarkan composer agar setiap render layouts.sidebar mendapat data dari SidebarComposer.
        \Illuminate\Support\Facades\View::composer(
            'layouts.sidebar',
            \App\Http\View\Composers\SidebarComposer::class
        );
    }
}

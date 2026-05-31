<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Surface N+1 queries during local/CI runs.
        Model::preventLazyLoading(!app()->isProduction());

        \Illuminate\Support\Facades\View::composer(
            'layouts.sidebar',
            \App\Http\View\Composers\SidebarComposer::class
        );
    }
}

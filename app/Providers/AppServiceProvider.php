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
        // Prevent lazy loading (N+1 queries) in non-production environments.
        // This will throw an exception if a relationship is lazy-loaded,
        // forcing eager loading to be used. Remove this in production.
        Model::preventLazyLoading(!app()->isProduction());
    }
}

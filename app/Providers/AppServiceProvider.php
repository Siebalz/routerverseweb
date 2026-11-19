<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Konstruktor DuitkuPop butuh string, jadi container perlu tahu cara membuatnya.
        $this->app->bind(\App\Services\DuitkuPop::class, fn () => \App\Services\DuitkuPop::make());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        // URL::forceScheme('https');

    }
}

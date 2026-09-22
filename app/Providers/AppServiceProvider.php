<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\AmcobEventsService::class);
        $this->app->bind(
            \App\Support\EventsDiscovery\EventsCatalogClient::class,
            \App\Services\AmcobEventsService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

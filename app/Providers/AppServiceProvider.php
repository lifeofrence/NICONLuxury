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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Attach API request logger to the 'api' middleware group
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('api', \App\Http\Middleware\ApiStatusLogger::class);
    }
}

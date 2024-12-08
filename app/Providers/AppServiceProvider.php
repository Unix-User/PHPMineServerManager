<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OllamaService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OllamaService::class, function ($app) {
            return new OllamaService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
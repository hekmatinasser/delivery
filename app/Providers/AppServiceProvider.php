<?php

namespace App\Providers;

use DragonCode\Support\Facades\Http\Url;
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
        if(!$this->app->environment('local')) {
            Url::forceScheme('https');
        }
    }
}

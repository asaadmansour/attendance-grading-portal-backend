<?php

namespace App\Providers;

use App\Support\Engagement\EngagementWindow;
use App\Support\Engagement\NullEngagementWindow;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            EngagementWindow::class,
            NullEngagementWindow::class,
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

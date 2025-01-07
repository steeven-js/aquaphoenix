<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Http\Controllers\MonthController;

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
        // Order::observe(OrderObserver::class);

        // Initialiser les statistiques mensuelles
        // MonthController::initializeAllMonths();
    }
}

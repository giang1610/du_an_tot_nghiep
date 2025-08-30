<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Order;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */


    public function boot()
    {
        View::composer('components.notification', function ($view) {
            $latestOrders = Order::latest()->take(10)->get(['id', 'order_number','status','payment_method','payment_status']);
            $view->with('latestOrders', $latestOrders);
        });
    }

}

<?php

namespace App\Filament\Widgets;


use App\Models\Shop\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // Nombre de commandes le mois dernier
        $lastMonthOrders = Order::whereMonth('published_at', Carbon::now()->subMonth()->month)->count();

        // Nombre de commandes ce mois-ci
        $thisMonthOrders = Order::whereMonth('published_at', Carbon::now()->month)->count();

        // Poids total des commandes ce mois-ci
        $month = Carbon::now()->month;

        $thisMonthTotalQty = Order::whereMonth('published_at', $month)
            ->with('items')
            ->get()
            ->sum(function ($order) {
                return $order->items->sum('qty');
            });

        $thisLastMonth = ucfirst(Carbon::now()->subMonth()->monthName);
        $thisMonth = ucfirst(Carbon::now()->monthName);

        // dd($thisLastMonth, $thisMonth);

        $label1 = 'Livraison ' . $thisLastMonth . ' : ';
        $label2 = 'Livraison ' . $thisMonth . ' : ';
        $label3 = 'Poid en (kg) ' . ' ' . $thisMonth . ' : ';

        return [
            Stat::make($label1, $lastMonthOrders)
                ->color('primary'),
            Stat::make($label2, $thisMonthOrders)
                ->color('primary'),
            Stat::make($label3, $thisMonthTotalQty)
                ->color('primary'),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Month;
use App\Models\Shop\Order;
use App\Models\OrderByMonth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class MonthController extends Controller
{
    /**
     * Méthode pour mettre à jour les données de mois
     *
     * @return void
     */
    public function month()
    {
        // Obtention de tous les orders avec le comptage par année et mois
        $orders = Order::select([
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month_number'),
            DB::raw('COUNT(*) as count')
        ])
            ->groupBy('year', 'month_number')
            ->get();

        foreach ($orders as $order) {
            // Recherche ou création du mois
            $month = Month::firstOrNew([
                'year' => $order->year,
                'month_number' => $order->month_number
            ]);

            // Mise à jour des données
            $month->fill([
                'month' => Carbon::createFromDate($order->year, $order->month_number, 1)->locale('fr')->monthName,
                'count' => $order->count,
            ])->save();
        }

        return redirect()->route('filament.admin.pages.dashboard');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Month;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonthController extends Controller
{
    public static function updateMonthStats(?string $month = null, ?string $year = null): void
    {
        if (!$month || !$year) {
            $date = Carbon::now();
            $month = $date->format('m');
            $year = $date->format('Y');
        }

        $count = Order::query()
            ->whereYear('delivered_date', $year)
            ->whereMonth('delivered_date', $month)
            ->where('status', 'livré')
            ->count();

        Month::updateOrCreate(
            [
                'year' => $year,
                'month_number' => $month,
            ],
            [
                'month' => Carbon::createFromDate($year, $month, 1)->locale('fr')->monthName,
                'count' => $count,
                'report_created_at' => now(),
            ]
        );
    }

    public static function initializeAllMonths(): void
    {
        // Récupérer tous les mois distincts où il y a des commandes
        $months = Order::query()
            ->select(DB::raw('DISTINCT YEAR(delivered_date) as year, MONTH(delivered_date) as month'))
            ->whereNotNull('delivered_date')
            ->where('status', 'livré')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Mettre à jour les statistiques pour chaque mois
        foreach ($months as $monthData) {
            self::updateMonthStats(
                str_pad($monthData->month, 2, '0', STR_PAD_LEFT),
                $monthData->year
            );
        }
    }

    public static function initializeCurrentAndLastMonth(): void
    {
        $currentDate = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        self::updateMonthStats(
            $currentDate->format('m'),
            $currentDate->format('Y')
        );

        self::updateMonthStats(
            $lastMonth->format('m'),
            $lastMonth->format('Y')
        );
    }

    public function month(): void
    {
        $currentDate = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Mettre à jour le mois en cours
        $this->updateMonthStats(
            $currentDate->format('m'),
            $currentDate->format('Y')
        );

        // Mettre à jour le mois précédent
        $this->updateMonthStats(
            $lastMonth->format('m'),
            $lastMonth->format('Y')
        );
    }
}

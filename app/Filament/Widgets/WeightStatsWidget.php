<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

/**
 * Widget affichant les statistiques de poids des commandes
 */
class WeightStatsWidget extends BaseWidget
{
    /**
     * Retourne les statistiques à afficher dans le widget
     *
     * @return array Les statistiques formatées pour l'affichage
     */
    protected function getStats(): array
    {
        // Calcul du poids total des commandes livrées pour le mois en cours
        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthWeight = Order::whereBetween('delivered_date', [
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        ])
            ->where('status', 'livré')
            ->withSum('items', 'qty')
            ->get()
            ->sum('items_sum_qty');

        // Calcul de la moyenne des poids sur les 6 derniers mois (uniquement commandes livrées)
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $monthlyWeights = Order::whereBetween('delivered_date', [
            $sixMonthsAgo,
            $lastMonthEnd
        ])
            ->where('status', 'livré')
            ->withSum('items', 'qty')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->delivered_date)->format('Y-m');
            })
            ->map(function ($month) {
                return $month->sum('items_sum_qty');
            });

        $last6MonthsAvg = $monthlyWeights->average() ?? 0;

        // Calcul du pourcentage d'évolution par rapport à la moyenne des 6 derniers mois
        $percentageChange = $last6MonthsAvg > 0
            ? (($currentMonthWeight - $last6MonthsAvg) / $last6MonthsAvg) * 100
            : 0;

        $trend = $percentageChange >= 0 ? '+' : '';
        $description = "{$trend}" . number_format($percentageChange, 1) . '% vs moyenne 6 mois';

        // Récupération du nombre de commandes en cours de progression
        $pendingOrders = Order::where('status', 'en progression')->count();

        // Retourne les trois statistiques principales
        return [
            Stat::make('Poids total livré (kg) par mois', number_format($currentMonthWeight, 0, ',', ' '))
                ->description($description)
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'danger'),

            Stat::make('Moyenne 6 derniers mois', number_format($last6MonthsAvg, 0, ',', ' ') . ' kg')
                ->icon('heroicon-o-scale')
                ->color('info'),

            Stat::make('Commandes en cours', $pendingOrders)
                ->description('En progression')
                ->color('warning')
                ->icon('heroicon-o-clock'),
        ];
    }
}

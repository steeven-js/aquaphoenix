<?php

namespace App\Filament\Widgets;

use App\Models\Month;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Livewire\Attributes\On;

/**
 * Widget affichant les statistiques de livraisons
 */
class DeliveriesStatsWidget extends BaseWidget
{
    /**
     * Position du widget dans le tableau de bord
     */
    protected static ?int $sort = 2;

    /**
     * Identifiant unique du widget
     */
    protected static ?string $widgetId = 'deliveries-stats-widget';

    /**
     * Rafraîchit les données du widget lors de l'événement 'refresh-deliveries-stats-widget'
     */
    #[On('refresh-deliveries-stats-widget')]
    public function refresh(): void
    {
        $this->updateTableQuery();
    }

    /**
     * Retourne les statistiques à afficher dans le widget
     *
     * @return array Les statistiques formatées pour l'affichage
     */
    protected function getStats(): array
    {
        // Initialisation des dates
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Récupération des statistiques du mois en cours
        $currentMonthStats = Month::where('year', $currentMonth->format('Y'))
            ->where('month_number', $currentMonth->format('m'))
            ->first();

        // Récupération des statistiques du mois précédent
        $lastMonthStats = Month::where('year', $lastMonth->format('Y'))
            ->where('month_number', $lastMonth->format('m'))
            ->first();

        // Calcul du poids total des livraisons du mois en cours (qty = poids en kg)
        $currentMonthWeight = Order::query()
            ->whereYear('delivered_date', $currentMonth->format('Y'))
            ->whereMonth('delivered_date', $currentMonth->format('m'))
            ->where('status', 'livré')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('SUM(order_items.qty) as total_weight')
            ->value('total_weight') ?? 0;

        // Retourne les trois statistiques principales
        return [
            Stat::make('Livraisons mois dernier', $lastMonthStats?->count ?? 0)
                ->description($lastMonth->locale('fr')->monthName . ' ' . $lastMonth->format('Y'))
                ->color('info')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Livraisons ce mois', $currentMonthStats?->count ?? 0)
                ->description($currentMonth->locale('fr')->monthName . ' ' . $currentMonth->format('Y'))
                ->color('success')
                ->icon('heroicon-o-truck'),

            Stat::make('Poids total du mois', number_format($currentMonthWeight, 0) . ' kg')
                ->description($currentMonth->locale('fr')->monthName . ' ' . $currentMonth->format('Y'))
                ->color('warning')
                ->icon('heroicon-o-scale'),
        ];
    }
}

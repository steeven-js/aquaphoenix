<?php

namespace App\Filament\Widgets;


use App\Models\Shop\Order;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Livraisons par mois';

    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Calculer les mois de l'année en cours avec la locale "fr"
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = strftime('%b', mktime(0, 0, 0, $i, 1));
        }

        // Utiliser une boucle pour obtenir le nombre total de commandes par mois
        $monthTotalOrders = [];

        for ($i = 1; $i <= 12; $i++) {
            $startOfMonth = Carbon::now()->startOfYear()->month($i);
            $endOfMonth = Carbon::now()->startOfYear()->month($i)->endOfMonth();

            $totalOrders = Order::whereBetween('published_at', [$startOfMonth, $endOfMonth])->count();

            $monthTotalOrders[] = $totalOrders;
        }

        // dd($monthTotalOrders);

        // Utiliser les données récupérées pour construire les tableaux
        return [
            'datasets' => [
                [
                    'label' => 'Livraisons',
                    'data' => $monthTotalOrders,
                    'fill' => 'start',
                ],
            ],
            'labels' => $months,
        ];
    }
}

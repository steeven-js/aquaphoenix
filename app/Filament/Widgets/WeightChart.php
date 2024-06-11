<?php

namespace App\Filament\Widgets;


use App\Models\Shop\Order;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;

class WeightChart extends ChartWidget
{
    protected static ?string $heading = 'Total Poids (kg) par mois';

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

        // Initialiser un tableau pour stocker les quantités mensuelles
        $monthlyQuantities = [];

        // Utiliser une boucle pour récupérer la quantité de livraisons par mois
        for ($i = 1; $i <= 12; $i++) {
            $startOfMonth = Carbon::now()->startOfYear()->month($i);
            $endOfMonth = Carbon::now()->startOfYear()->month($i)->endOfMonth();

            // Récupérer la somme des quantités de livraison pour le mois actuel
            $totalQty = Order::whereBetween('published_at', [$startOfMonth, $endOfMonth])
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->sum('order_items.qty');

            // Ajouter la somme au tableau mensuel
            $monthlyQuantities[] = $totalQty;
        }

        // Utiliser les données récupérées pour construire les tableaux
        return [
            'datasets' => [
                [
                    'label' => 'Poids (kg) par mois',
                    'data' => $monthlyQuantities,
                    'fill' => 'start',
                ],
            ],
            'labels' => $months,
        ];
    }
}

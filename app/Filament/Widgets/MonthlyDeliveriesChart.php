<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyDeliveriesChart extends ChartWidget
{
    protected static ?string $heading = 'Nombre de livraisons par mois (6 derniers mois)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();

        $data = Order::query()
            ->select([
                DB::raw('YEAR(delivered_date) as year'),
                DB::raw('MONTH(delivered_date) as month'),
                DB::raw('COUNT(*) as total_deliveries')
            ])
            ->whereNotNull('delivered_date')
            ->where('status', 'livré')
            ->where('delivered_date', '>=', $sixMonthsAgo)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'label' => $date->locale('fr')->isoFormat('MMMM YYYY'),
                    'value' => $item->total_deliveries,
                ];
            });

        return [
            'datasets' => [
                [
                    'label' => 'Livraisons',
                    'data' => $data->pluck('value')->toArray(),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}

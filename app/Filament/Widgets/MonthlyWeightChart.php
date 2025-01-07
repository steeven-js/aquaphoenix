<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyWeightChart extends ChartWidget
{
    protected static ?string $heading = 'Poids total (kg) par mois (6 derniers mois)';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();

        $data = Order::query()
            ->select([
                DB::raw('YEAR(delivered_date) as year'),
                DB::raw('MONTH(delivered_date) as month'),
                DB::raw('SUM((SELECT SUM(qty) FROM order_items WHERE order_items.order_id = orders.id)) as total_weight')
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
                    'value' => $item->total_weight ?? 0,
                ];
            });

        return [
            'datasets' => [
                [
                    'label' => 'Poids total (kg)',
                    'data' => $data->pluck('value')->toArray(),
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FF6384',
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

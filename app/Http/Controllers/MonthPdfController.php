<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MonthPdfController extends Controller
{
    public function generatePdf(string $month, string $year)
    {
        $orders = Order::query()
            ->with(['customer', 'items.product'])
            ->whereYear('delivered_date', $year)
            ->whereMonth('delivered_date', $month)
            ->where('status', 'livré')
            ->orderBy('delivered_date')
            ->get();

        $totalWeight = $orders->sum(function ($order) {
            return $order->items->sum('qty');
        });

        $date = Carbon::createFromDate($year, $month, 1);
        $monthName = $date->locale('fr')->monthName;

        $pdf = Pdf::loadView('pdf.month-deliveries', [
            'orders' => $orders,
            'month' => $monthName,
            'year' => $year,
            'totalWeight' => $totalWeight,
        ]);

        return $pdf->download("livraisons-$monthName-$year.pdf");
    }
}

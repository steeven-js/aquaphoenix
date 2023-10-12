<?php

namespace App\Http\Controllers;

use App\Models\Month;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    public function printOrder($id)
    {
        $order = Order::with('customer', 'items')->find($id);

        if (!$order) {
            abort(404);
        }

        $year = Carbon::parse($order->published_at)->format('Y');
        $orderData = [];

        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $orderData[] = [
                    'product_id' => $item->product_id,
                    'Product Name' => $product->name,
                    'Description' => $product->description,
                    'Quantity' => $item->qty,
                ];
            }
        }

        $totalQuantity = 0;

        foreach ($orderData as $item) {
            $totalQuantity += $item['Quantity'];
        }

        $formattedCreationDate = $order->getFormattedPublishedDate();
        $formattedDeliveredDate = $order->getFormattedDeliveredDate();

        $pdf = PDF::loadView('pages.rapport.pdf.livraison', compact('order', 'orderData', 'formattedCreationDate', 'formattedDeliveredDate', 'totalQuantity'));
        $pdfFileName = $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $pdf->save($pdfPath);
        $url = env('APP_URL') . Storage::url("pdf/{$year}/rapport-livraison/{$pdfFileName}");

        $order->url = $url;
        $order->save();

        $mailData = [
            'order' => $order,
            'number' => $order->number,
            'pdfPath' => $pdfPath,
            'orderUrl' => route('order.print', $order->id),
            'url' => $url,
            'formattedCreationDate' => $order->getFormattedPublishedDate(),
            'formattedDeliveredDate' => $order->getFormattedDeliveredDate(),
            'totalQuantity' => $totalQuantity,
        ];

        $this->sendEmail($mailData);

        return $pdf->stream($pdfFileName);
    }

    public function livraison($id)
    {
        $order = Order::with('customer', 'items')->find($id);

        if (!$order) {
            abort(404);
        }

        $year = Carbon::parse($order->published_at)->format('Y');
        $orderData = [];

        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $orderData[] = [
                    'product_id' => $item->product_id,
                    'Product Name' => $product->name,
                    'Description' => $product->description,
                    'Quantity' => $item->qty,
                ];
            }
        }

        $totalQuantity = 0;

        foreach ($orderData as $item) {
            $totalQuantity += $item['Quantity'];
        }

        $formattedCreationDate = $order->getFormattedPublishedDate();
        $formattedDeliveredDate = $order->getFormattedDeliveredDate();

        $pdf = PDF::loadView('pages.rapport.pdf.livraison', compact('order', 'orderData', 'formattedCreationDate', 'formattedDeliveredDate', 'totalQuantity'));
        $pdfFileName = $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $pdf->save($pdfPath);
        $url = env('APP_URL') . Storage::url("pdf/{$year}/rapport-livraison/{$pdfFileName}");

        $order->url = $url;
        $order->save();

        $storage = Storage::url("pdf/{$year}/rapport-livraison/{$pdfFileName}");

        return [
            'pdfPath' => $pdfPath,
            'storage' => $storage,
            'url' => $url,
        ];
    }

    public function ordersByMonth($month, $year)
    {
        $orders = Order::with('customer', 'items')
            ->whereMonth('published_at', $month)
            ->whereYear('published_at', $year)
            ->get();

        $ordersData = [];

        foreach ($orders as $order) {
            $orderData = [];

            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $orderData[] = [
                        'Product ID' => $item->product_id,
                        'Product Name' => $product->name,
                        'Description' => $product->description,
                        'Quantity' => $item->qty,
                    ];
                }
            }

            $formattedCreationDate = $order->getFormattedPublishedDate();
            $formattedDeliveredDate = $order->getFormattedDeliveredDate();

            $ordersData[] = [
                'Order ID' => $order->id,
                'Order Number' => $order->number,
                'Customer Name' => $order->customer->name,
                'Creation Date' => $formattedCreationDate,
                'Delivered Date' => $formattedDeliveredDate,
                'Order Items' => $orderData,
            ];
        }

        $totalQuantity = 0;

        foreach ($ordersData as $order) {
            foreach ($order['Order Items'] as $item) {
                if (isset($item['Quantity'])) {
                    $totalQuantity += $item['Quantity'];
                }
            }
        }

        $monthName = $this->translateToFrench($month, 'month');
        $MonthTable = Month::where('month_number', $month)->where('year', $year)->first();

        $pdf = PDF::loadView('pages.rapport.pdf.orders_by_month', [
            'monthName' => $monthName,
            'year' => $year,
            'ordersData' => $ordersData,
            'totalQuantity' => $totalQuantity,
        ]);

        $pdfFileName = $month . '-' . $year . '.pdf';
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $pdf->save($pdfPath);

        $this->sendNotificationForMonthReport($monthName, $MonthTable, $year);

        return $pdf->stream('orders_by_month.pdf');
    }

    public function translateToFrench($value, $type)
    {
        if ($type === 'month') {
            $months = [
                1 => 'Janvier',
                2 => 'Février',
                3 => 'Mars',
                4 => 'Avril',
                5 => 'Mai',
                6 => 'Juin',
                7 => 'Juillet',
                8 => 'Août',
                9 => 'Septembre',
                10 => 'Octobre',
                11 => 'Novembre',
                12 => 'Décembre',
            ];

            return $months[$value] ?? '';
        } elseif ($type === 'dayOfWeek') {
            $daysOfWeek = [
                0 => 'dimanche',
                1 => 'lundi',
                2 => 'mardi',
                3 => 'mercredi',
                4 => 'jeudi',
                5 => 'vendredi',
                6 => 'samedi',
            ];

            return $daysOfWeek[$value] ?? '';
        }

        return ''; // Si le type n'est pas pris en charge, retourne une chaîne vide
    }

    private function sendEmail($mailData)
    {
        $pdfPath = $result['pdfPath'];
        $storage = $result['storage'];
        $url = $result['url'];

        Mail::to('lianajacques18@gmail.com')
            ->cc(['liana.jacques@aquaphoenix.fr', 'jacques.steeven@gmail.com'])
            ->send(new LivraisonMail($mailData, $storage, $url));
    }
}

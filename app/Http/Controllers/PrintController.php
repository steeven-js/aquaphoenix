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
        $order = $this->getOrderById($id);
        $year = $this->getYearFromOrder($order);

        $orderData = $this->getOrderData($order);
        $totalQuantity = $this->getTotalQuantity($orderData);

        $pdf = $this->generateOrderPDF($order, $orderData, $year);

        $pdfFileName = $this->getPDFFileName($order);
        $pdfPath = $this->savePDFAndGetPath($pdf, $pdfFileName, $year);
        $url = $this->getUrlForPDF($pdfFileName, $year);

        $this->updateOrderURL($order, $url);

        return $pdf->stream($pdfFileName);
    }

    public function livraison($id)
    {
        $order = $this->getOrderById($id);
        $year = $this->getYearFromOrder($order);

        $orderData = $this->getOrderData($order);
        $totalQuantity = $this->getTotalQuantity($orderData);

        $pdf = $this->generateOrderPDF($order, $orderData, $year);

        $pdfFileName = $this->getPDFFileName($order);
        $pdfPath = $this->savePDFAndGetPath($pdf, $pdfFileName, $year);
        $url = $this->getUrlForPDF($pdfFileName, $year);

        $storage = Storage::url('pdf/' . $year . '/' . 'rapport-livraison' . '/' . $pdfFileName);

        $this->updateOrderURL($order, $url);

        return [
            'pdfPath' => $pdfPath,
            'storage' => $storage,
            'url' => $url,
        ];
    }

    public function ordersByMonth($month, $year)
    {
        $orders = $this->getOrdersByMonthAndYear($month, $year);

        $ordersData = $this->getOrdersData($orders);
        $totalQuantity = $this->getMonthTotalQuantity($ordersData);

        $monthName = $this->translateToFrench($month, 'month');

        $MonthTable = Month::where('month_number', $month)->where('year', $year)->first();

        $pdf = $this->generateOrdersByMonthPDF($monthName, $year, $ordersData, $totalQuantity);

        $pdfFileName = $this->getOrdersByMonthPDFFileName($month, $year);
        $pdfPath = $this->savePDFAndGetPath($pdf, $pdfFileName, $year);

        $this->updateMonthTable($month, $year);

        $this->sendNotificationForMonthReport($monthName, $MonthTable, $year);

        return $pdf->stream('orders_by_month.pdf');
    }

    // Helper functions
    private function getOrderById($id)
    {
        $order = Order::with('customer', 'items')->find($id);

        if (!$order) {
            abort(404);
        }

        return $order;
    }

    private function getYearFromOrder($order)
    {
        if (!empty($order->published_at)) {
            return Carbon::parse($order->published_at)->format('Y');
        }

        dd('Published date is empty.');
    }

    private function getOrderData($order)
    {
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

        return $orderData;
    }

    private function getTotalQuantity($orderData)
    {
        $totalQuantity = 0;
        foreach ($orderData as $item) {
            $totalQuantity = $totalQuantity + $item['Quantity'];
        }
        return $totalQuantity;

    }

    private function getMonthTotalQuantity($orderData)
    {
        $totalQuantity = 0;

        foreach ($orderData as $order) {
            foreach ($order['Order Items'] as $item) {
                if (isset($item['Quantity'])) {
                    $totalQuantity += $item['Quantity'];
                }
            }
        }
        return $totalQuantity;
    }

    private function generateOrderPDF($order, $orderData, $year)
    {
        $formattedCreationDate = $order->getFormattedPublishedDate();

        $formattedDeliveredDate = $order->getFormattedDeliveredDate();

        $totalQuantity = $this->getTotalQuantity($orderData);

        return PDF::loadView('pages.rapport.pdf.livraison', compact('order', 'orderData', 'formattedCreationDate', 'formattedDeliveredDate', 'totalQuantity'));
    }

    private function getPDFFileName($order)
    {
        return $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
    }

    private function savePDFAndGetPath($pdf, $pdfFileName, $year)
    {
        $pdfDirectory = 'pdf/' . $year . '/' . 'rapport-livraison' . '/';
        $fullPdfDirectory = storage_path('app/public/' . $pdfDirectory);

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfPath = $fullPdfDirectory . $pdfFileName;

        $pdf->save($pdfPath);

        return $pdfPath;
    }

    private function getUrlForPDF($pdfFileName, $year)
    {
        $app_url = env('APP_URL');
        $storage = Storage::url('pdf/' . $year . '/' . 'rapport-livraison' . '/' . $pdfFileName);
        return $app_url . $storage;
    }

    private function updateOrderURL($order, $url)
    {
        $order->url = $url;
        $order->save();
    }

    private function getOrdersByMonthAndYear($month, $year)
    {
        return Order::with('customer', 'items')
            ->whereMonth('published_at', $month)
            ->whereYear('published_at', $year)
            ->get();
    }

    private function getOrdersData($orders)
    {
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
        return $ordersData;
    }

    private function generateOrdersByMonthPDF($monthName, $year, $ordersData, $totalQuantity)
    {
        return PDF::loadView('pages.rapport.pdf.orders_by_month', [
            'monthName' => $monthName,
            'year' => $year,
            'ordersData' => $ordersData,
            'totalQuantity' => $totalQuantity,
        ]);
    }

    private function getOrdersByMonthPDFFileName($month, $year)
    {
        return $month . '-' . $year . '-' . '.pdf';
    }

    private function updateMonthTable($month, $year)
    {
        $MonthTable = Month::where('month_number', $month)->where('year', $year)->first();
        if ($MonthTable->report_status == false) {
            $MonthTable->report_status = true;
            $MonthTable->report_created_at = Carbon::now();
            $MonthTable->save();
        }
    }

    private function sendNotificationForMonthReport($monthName, $MonthTable, $year)
    {
        Notification::make()
            ->title('Nouvelle commande')
            ->icon('heroicon-o-shopping-bag')
            ->body("** Rapport du mois {$monthName} créé le {$MonthTable->report_created_at} **")
            ->sendToDatabase(auth()->user());
    }

    public function translateToFrench($value, $type)
    {
        if ($type === 'month') {
            $months = [
                1 => 'janvier',
                2 => 'février',
                3 => 'mars',
                4 => 'avril',
                5 => 'mai',
                6 => 'juin',
                7 => 'juillet',
                8 => 'août',
                9 => 'septembre',
                10 => 'octobre',
                11 => 'novembre',
                12 => 'décembre',
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
}

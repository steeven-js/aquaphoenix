<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Month;
use App\Models\Shop\Order;
use App\Mail\LivraisonMail;
use App\Models\OrderProduct;
use App\Models\Shop\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use App\Http\Controllers\PrintController;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\Shop\OrderResource;
use App\Notifications\ReportDeliveredNotification;

class OrderController extends Controller
{
    public function generatePdf(Order $order)
    {
        $order = Order::with('customer', 'items')->findOrFail($order->id);
        $year = Carbon::parse($order->published_at)->format('Y');
        $orderData = $this->getOrderData($order);
        $totalQuantity = $this->calculateTotalQuantity($orderData);
        $formattedCreationDate = $order->getFormattedPublishedDate();
        $formattedDeliveredDate = $order->getFormattedDeliveredDate();

        $pdf = PDF::loadView('pages.rapport.pdf.livraison', compact('order', 'orderData', 'formattedCreationDate', 'formattedDeliveredDate', 'totalQuantity'));

        $pdfPath = $this->savePdf($pdf, $order, $year);

        $order->url = $this->generatePdfUrl($year, $pdfPath);
        $order->save();

        return [
            'pdf' => $pdf,
            'pdfPath' => $pdfPath,
            'storage' => Storage::url($pdfPath),
            'url' => $order->url,
        ];
    }

    public function openPdf(Order $order)
    {
        $pdfData = $this->generatePdf($order);
        $pdf = $pdfData['pdf'];
        $pdfFileName = $order->id . '-' . Carbon::parse($order->published_at)->format('d-m-Y') . '-' . $order->customer->id . '.pdf';

        return $pdf->stream($pdfFileName);
    }

    public function mailLivraison(Order $order)
    {
        if (!$order) {
            abort(404);
        }

        $pdfData = $this->generatePdf($order);
        $year = Carbon::parse($order->published_at)->format('Y');
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");
        $pdfFileName = $order->id . '-' . Carbon::parse($order->published_at)->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
        $storage = Storage::url("pdf/{$year}/rapport-livraison/{$pdfFileName}");
        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $url = env('APP_URL') . $storage;

        if (empty($order->published_at)) {
            dd('La date de publication est vide.');
        }

        $orderData = $this->getOrderData($order);
        $totalQuantity = $this->calculateTotalQuantity($orderData);

        $mailData = [
            'order' => $order,
            'number' => $order->number,
            'pdfPath' => $pdfPath,
            'orderUrl' => route('livraison.mail', $order->id),
            'url' => $url,
            'formattedCreationDate' => $order->getFormattedPublishedDate(),
            'formattedDeliveredDate' => $order->getFormattedDeliveredDate(),
            'totalQuantity' => $totalQuantity,
        ];

        Mail::to('kisama972@gmail')
            ->cc(['jacques.steeven@gmail.com'])
            ->send(new LivraisonMail($mailData, $storage, $url));

        $this->sendNotification($order);

        $order->report_delivered = 1;
        $order->report_delivered_date = now();
        $order->save();

        return redirect()->route('filament.admin.pages.dashboard');
    }

    private function sendNotification($order)
    {
        $recipient = auth()->user();

        Notification::make()
            ->title('Mail envoyé avec succès le' . ' ' . $order->report_delivered_date)
            ->actions([
                Action::make('Voir')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase($recipient);
    }

    public function ordersByMonth($month, $year)
    {
        $orders = Order::with('customer', 'items')
            ->whereMonth('published_at', $month)
            ->whereYear('published_at', $year)
            ->get();

        $ordersData = $this->getOrdersData($orders);

        $totalQuantity = $this->calculateTotalQuantityInOrdersData($ordersData);

        $monthName = $this->translateToFrench($month, 'month');
        $MonthTable = Month::where('month_number', $month)->where('year', $year)->first();

        $pdf = PDF::loadView('pages.rapport.pdf.orders_by_month', [
            'monthName' => $monthName,
            'year' => $year,
            'ordersData' => $ordersData,
            'totalQuantity' => $totalQuantity,
        ]);

        $pdfFileName = $month . '-' . $year . '.pdf';
        $pdfDirectory = "pdf/{$year}/rapport-mensuel/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfPath = $fullPdfDirectory . $pdfFileName;

        $pdf->save($pdfPath);

        return $pdf->stream($pdfFileName);
    }

    private function getOrderData(Order $order)
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

    private function calculateTotalQuantity(array $orderData)
    {
        return array_sum(array_column($orderData, 'Quantity'));
    }

    private function savePdf($pdf, Order $order, $year)
    {
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $pdfFileName = $order->id . '-' . Carbon::parse($order->published_at)->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfPath = $fullPdfDirectory . $pdfFileName;

        $pdf->save($pdfPath);

        return $pdfPath;
    }

    private function generatePdfUrl($year, $pdfPath)
    {
        $storage = Storage::url("pdf/{$year}/rapport-livraison/{$pdfPath}");
        return env('APP_URL') . $storage;
    }

    private function getOrdersData($orders)
    {
        $ordersData = [];

        foreach ($orders as $order) {
            $orderData = $this->getOrderData($order);
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

    private function calculateTotalQuantityInOrdersData(array $ordersData)
    {
        $totalQuantity = 0;

        foreach ($ordersData as $order) {
            foreach ($order['Order Items'] as $item) {
                if (isset($item['Quantity'])) {
                    $totalQuantity += $item['Quantity'];
                }
            }
        }

        return $totalQuantity;
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

        return '';
    }

    public function updateOrderStatus()
    {
        $orders = Order::all();

        foreach ($orders as $order) {
            $originalStatus = $order->status;

            if ($order->delivered_date <= Carbon::now()->toDateString()) {
                $order->status = 'livré';
                $order->save();

                if ($order->status !== $originalStatus) {
                    $recipient = auth()->user();

                    Notification::make()
                        ->title('La commande ' . $order->number . ' est livrée')
                        ->actions([
                            Action::make('Voir')
                                ->url(OrderResource::getUrl('edit', ['record' => $order])),
                        ])
                        ->sendToDatabase($recipient);
                }
            }
        }

        return redirect()->route('filament.admin.resources.shop.orders.index');
    }

    public function generateAllPdfs()
    {
        $orders = Order::all();

        foreach ($orders as $order) {
            $this->generatePdf($order);
        }
    }

    public function generateAllOrdersByMonthPdfs()
    {
        $months = Month::where('count', '>', 0)->get();

        foreach ($months as $month) {
            $this->ordersByMonth($month->month_number, $month->year);
        }
    }

    public function updateNumber()
    {
        $orders = Order::all();

        foreach ($orders as $order) {
            $order->number = 'CMD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
            $order->save();
        }
    }

}

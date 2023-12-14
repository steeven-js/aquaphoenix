<?php

namespace App\Http\Controllers;

use App\Models\Month;
use App\Models\Shop\Order;
use App\Mail\LivraisonMail;
use App\Models\Shop\Product;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\Shop\OrderResource;

class PrintController extends Controller
{
    /**
     * Générer le rapport de livraison et le sauvegarder dans le dossier storage/app/public/pdf
     *
     * @param Order $order
     * @return void
     */
    public function generatePdf(Order $order)
    {
        $order = Order::with('customer', 'items')->findOrFail($order->id);

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
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $pdfFileName = $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $pdf->save($pdfPath);
        $storage = Storage::url("pdf/{$year}/rapport-livraison/{$pdfFileName}");
        $url = env('APP_URL') . $storage;

        $order->url = $url;
        $order->save();

        return [
            'pdf' => $pdf,
            'pdfPath' => $pdfPath,
            'storage' => $storage,
            'url' => $url,
        ];
    }

    /**
     * Utiliser la méthode generatePdf pour générer le rapport de livraison et l'ouvrire dans le navigateur
     *
     * @param Order $order
     * @return void
     */
    public function openPdf(Order $order)
    {
        // Generate the PDF
        $pdfData = $this->generatePdf($order);
        $pdf = $pdfData['pdf'];
        $pdfFileName = $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';

        // Return the PDF to be streamed
        return $pdf->stream($pdfFileName);
    }

    /**
     * Envoyer le rapport de livraison par mail
     *
     * @param Order $order
     * @return void
     */
    public function mailLivraison(Order $order)
    {

        // Vérifiez si $order est null et renvoyez une erreur 404 en conséquence.
        if (!$order) {
            abort(404);
        }

        // Générer le rapport de livraison dans le dossier storage/app/public/pdf
        $pdfData = $this->generatePdf($order);

        $year = Carbon::parse($order->published_at)->format('Y');
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        $pdfFileName = $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
        $storage = Storage::url("pdf/{$year}/rapport-livraison/{$pdfFileName}");

        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $url = env('APP_URL') . $storage;

        // Vérifiez si la date de publication est vide et affichez un message de débogage si c'est le cas.
        if (empty($order->published_at)) {
            dd('La date de publication est vide.');
        }

        // Déclarez $orderData et $totalQuantity en dehors de la boucle foreach.
        $orderData = [];
        $totalQuantity = 0;

        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);

            if ($product) {
                $orderData[] = [
                    'product_id' => $item->product_id,
                    'Nom du produit' => $product->name,
                    'Description' => $product->description,
                    'Quantité' => $item->qty,
                ];

                $totalQuantity += $item->qty;
            }
        }

        $mailData = [
            'order' => $order,
            'number' => $order->number,
            'pdfPath' => $pdfPath,
            'orderUrl' => route('livraison.mail', $order->id), // Assurez-vous que cette route est définie
            'url' => $url,
            'formattedCreationDate' => $order->getFormattedPublishedDate(), // En supposant que cette méthode existe
            'formattedDeliveredDate' => $order->getFormattedDeliveredDate(), // En supposant que cette méthode existe
            'totalQuantity' => $totalQuantity,
        ];

        // dd($mailData, $storage, $url);

        Mail::to('kisama972@gmail')
            ->cc(['jacques.steeven@gmail.com'])
            ->send(new LivraisonMail($mailData, $storage, $url));

        $this->sendNotification($order);

        $order->report_delivered = 1;
        $order->report_delivered_date = now();
        $order->save();

        return redirect()->route('filament.admin.pages.dashboard');
    }

    /**
     * Notification après l'envoi du rapport de livraison par mail
     *
     * @param [type] $order
     * @return void
     */
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

    /**
     * Générer le rapport de livraison par mois et l'ouvrire dans le navigateur
     *
     * @param [type] $month
     * @param [type] $year
     * @return void
     */
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
        $pdfDirectory = "pdf/{$year}/rapport-mensuel/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $pdf->save($pdfPath);

        return $pdf->stream($pdfFileName);
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
}

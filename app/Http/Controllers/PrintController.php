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
     * @return array
     */
    public function generatePdf(Order $order)
    {
        // Récupérer les détails de la commande avec le client et les articles associés
        $order = Order::with('customer', 'items')->findOrFail($order->id);

        // Extraire l'année de la date de publication de la commande
        $year = Carbon::parse($order->published_at)->format('Y');

        // Initialiser un tableau pour stocker les données de la commande
        $orderData = [];

        // Boucler à travers les articles de la commande pour obtenir les détails du produit
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

        // Calculer la quantité totale commandée
        $totalQuantity = array_sum(array_column($orderData, 'Quantity'));

        // Formater les dates de création et de livraison de la commande
        $formattedCreationDate = $order->getFormattedPublishedDate();
        $formattedDeliveredDate = $order->getFormattedDeliveredDate();

        // Charger la vue PDF avec les données de la commande
        $pdf = PDF::loadView('pages.rapport.pdf.livraison', compact('order', 'orderData', 'formattedCreationDate', 'formattedDeliveredDate', 'totalQuantity'));

        // Définir le répertoire de sauvegarde du PDF
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $pdfFileName = $order->id . '-' . Carbon::parse($order->published_at)->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        // Créer le répertoire s'il n'existe pas
        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        // Définir le chemin complet du fichier PDF
        $pdfPath = $fullPdfDirectory . $pdfFileName;

        // Sauvegarder le PDF
        $pdf->save($pdfPath);

        // Générer l'URL pour le PDF sauvegardé
        $storage = Storage::url("pdf/{$year}/rapport-livraison/{$pdfFileName}");
        $url = env('APP_URL') . $storage;

        // Mettre à jour l'URL de la commande dans la base de données
        $order->url = $url;
        $order->save();

        // Retourner les informations du PDF généré
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
        // Générer le PDF
        $pdfData = $this->generatePdf($order);
        $pdf = $pdfData['pdf'];
        $pdfFileName = $order->id . '-' . Carbon::parse($order->published_at)->format('d-m-Y') . '-' . $order->customer->id . '.pdf';

        // Retourner le PDF à diffuser en streaming
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
        // Vérifier si $order est null et renvoyer une erreur 404 en conséquence.
        if (!$order) {
            abort(404);
        }

        // Générer le rapport de livraison dans le dossier storage/app/public/pdf
        $pdfData = $this->generatePdf($order);

        // Extraire l'année de la date de publication de la commande
        $year = Carbon::parse($order->published_at)->format('Y');
        $pdfDirectory = "pdf/{$year}/rapport-livraison/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        $pdfFileName = $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';
        $storage = Storage::url("pdf/{$year}/rapport-livraison/{$pdfFileName}");

        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $url = env('APP_URL') . $storage;

        // Vérifier si la date de publication est vide et afficher un message de débogage si c'est le cas.
        if (empty($order->published_at)) {
            dd('La date de publication est vide.');
        }

        // Déclarer $orderData et $totalQuantity en dehors de la boucle foreach.
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

        // Préparer les données pour le courrier électronique
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

        // Envoyer le courrier électronique avec le rapport de livraison
        Mail::to('kisama972@gmail')
            ->cc(['jacques.steeven@gmail.com'])
            ->send(new LivraisonMail($mailData, $storage, $url));

        // Envoyer une notification après l'envoi du courrier électronique
        $this->sendNotification($order);

        // Marquer la commande comme rapport délivré
        $order->report_delivered = 1;
        $order->report_delivered_date = now();
        $order->save();

        // Rediriger vers le tableau de bord
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
        // Récupérer le destinataire à partir de l'utilisateur actuel
        $recipient = auth()->user();

        // Créer et envoyer la notification
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
        // Récupérer les commandes du mois spécifié
        $orders = Order::with('customer', 'items')
            ->whereMonth('published_at', $month)
            ->whereYear('published_at', $year)
            ->get();

        $ordersData = [];

        // Parcourir les commandes pour obtenir les détails
        foreach ($orders as $order) {
            $orderData = [];

            // Parcourir les articles de la commande pour obtenir les détails du produit
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

            // Formater les dates de création et de livraison de la commande
            $formattedCreationDate = $order->getFormattedPublishedDate();
            $formattedDeliveredDate = $order->getFormattedDeliveredDate();

            // Stocker les données de la commande
            $ordersData[] = [
                'Order ID' => $order->id,
                'Order Number' => $order->number,
                'Customer Name' => $order->customer->name,
                'Creation Date' => $formattedCreationDate,
                'Delivered Date' => $formattedDeliveredDate,
                'Order Items' => $orderData,
            ];
        }

        // Calculer la quantité totale commandée
        $totalQuantity = 0;

        foreach ($ordersData as $order) {
            foreach ($order['Order Items'] as $item) {
                if (isset($item['Quantity'])) {
                    $totalQuantity += $item['Quantity'];
                }
            }
        }

        // Traduire le numéro du mois en français
        $monthName = $this->translateToFrench($month, 'month');

        // Récupérer les informations du mois à partir du modèle Month
        $MonthTable = Month::where('month_number', $month)->where('year', $year)->first();

        // Charger la vue PDF pour le rapport par mois
        $pdf = PDF::loadView('pages.rapport.pdf.orders_by_month', [
            'monthName' => $monthName,
            'year' => $year,
            'ordersData' => $ordersData,
            'totalQuantity' => $totalQuantity,
        ]);

        // Définir le nom du fichier PDF et le répertoire de sauvegarde
        $pdfFileName = $month . '-' . $year . '.pdf';
        $pdfDirectory = "pdf/{$year}/rapport-mensuel/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        // Créer le répertoire s'il n'existe pas
        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        // Définir le chemin complet du fichier PDF
        $pdfPath = $fullPdfDirectory . $pdfFileName;

        // Sauvegarder le PDF
        $pdf->save($pdfPath);

        // Retourner le PDF à diffuser en streaming
        return $pdf->stream($pdfFileName);
    }

    /**
     * Traduire une valeur en français en fonction du type (mois, jour de la semaine, etc.)
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    public function translateToFrench($value, $type)
    {
        if ($type === 'month') {
            // Tableau de correspondance des mois en français
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

            // Retourner le mois correspondant en français
            return $months[$value] ?? '';
        } elseif ($type === 'dayOfWeek') {
            // Tableau de correspondance des jours de la semaine en français
            $daysOfWeek = [
                0 => 'dimanche',
                1 => 'lundi',
                2 => 'mardi',
                3 => 'mercredi',
                4 => 'jeudi',
                5 => 'vendredi',
                6 => 'samedi',
            ];

            // Retourner le jour de la semaine correspondant en français
            return $daysOfWeek[$value] ?? '';
        }

        return ''; // Si le type n'est pas pris en charge, retourne une chaîne vide
    }
}

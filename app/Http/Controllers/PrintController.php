<?php

namespace App\Http\Controllers;


use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
    /**
     * Générer le PDF de la commande
     */
    public function printOrder($id)
    {
        // Je récupère la commande
        $order = Order::with('customer', 'items')->find($id);

        // Check if published_at is not empty
        if (!empty($order->published_at)) {
            $carbonDate = Carbon::parse($order->published_at);
            $year = $carbonDate->format('Y');
            // dd($year);
        } else {
            // Handle the case where published_at is empty
            dd('Published date is empty.');
        }

        // Si la commande n'existe pas, je retourne une erreur 404
        if (!$order) {
            abort(404);
        }

        // J'initialise un tableau vide qui contiendra les données de la commande
        $orderData = [];
        // J'initialise une variable qui contiendra la quantité totale de produits
        $totalQuantity = 0;

        // Je boucle sur les produits de la commande
        foreach ($order->items as $item) {
            // Je récupère le produit de la commande
            $product = Product::find($item->product_id); // Je récupère l'id du produit

            // Si le produit existe, je l'ajoute au tableau de données de la commande
            if ($product) {

                // J'ajoute les données du produit au tableau de données de la commande
                $orderData[] = [
                    'product_id' => $item->product_id, // Je récupère l'id du produit
                    'Product Name' => $product->name, // Je récupère le nom du produit
                    'Description' => $product->description, // Je récupère la description du produit
                    'Quantity' => $item->qty, // Je récupère la quantité du produit
                ];
                $totalQuantity += $item->qty; // Je calcule la quantité totale de produits
            }
        }

        // Je stocke la date de création de la commande formatée
        $formattedCreationDate = $order->getFormattedPublishedDate();

        // Je stocke la date de livraison de la commande formatée
        $formattedDeliveredDate = $order->getFormattedDeliveredDate();

        // Chemin du répertoire pour le PDF, avec un sous-dossier pour le mois et l'année
        $pdfDirectory = 'pdf/' . $year . '/' . 'rapport-livraison' . '/';
        $fullPdfDirectory = storage_path('app/public/' . $pdfDirectory);

        // Vérifiez si le répertoire existe, sinon, créez-le
        if (!file_exists($fullPdfDirectory)) {
            // 0755 est le mode par défaut pour les répertoires, il permet de créer des répertoires lisibles et accessibles en écriture pour tout le monde. True permet de créer les répertoires parents si nécessaire
            mkdir($fullPdfDirectory, 0755, true);
        }

        // Génération du PDF avec les données de la commande
        $pdf = PDF::loadView('pages.rapport.pdf.livraison', compact('order', 'orderData', 'formattedCreationDate', 'formattedDeliveredDate', 'totalQuantity'));

        // Enregistrement du PDF dans le dossier storage/app/public dans un sous-dossier nommé avec le mois et de l'année de la commande
        $pdfFileName = $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';

        // Si le fichier existe déjà, on le supprime puis on le recrée pour éviter d'avoir des fichiers obsolètes ou pour les commandes modifiées.
        if (file_exists($fullPdfDirectory . $pdfFileName)) {
            unlink($fullPdfDirectory . $pdfFileName);
        }

        // Enregistrement du PDF dans le dossier storage/app/public dans un sous-dossier nommé avec le mois et de l'année de la commande
        $pdfPath = $fullPdfDirectory . $pdfFileName;

        $app_url = env('APP_URL');
        $storage = Storage::url($pdfDirectory . $pdfFileName);
        $url = $app_url . $storage;

        // Enregistrement du PDF dans la dossier storage/app/public
        $pdf->save($pdfPath);

        $pdf->stream($pdfFileName);

        // Mise à jour de l'URL dans la base de données
        $order->url = $url;
        $order->save(); // Enregistrez les modifications dans la base de données

        return $pdf->stream($pdfFileName);
    }

    /**
     * Générer le PDF de la commande
     */
    public function livraison($id)
    {
        // Je récupère la commande
        $order = Order::with('customer', 'items')->find($id);

        // Check if published_at is not empty
        if (!empty($order->published_at)) {
            $carbonDate = Carbon::parse($order->published_at);
            $year = $carbonDate->format('Y');
            // dd($year);
        } else {
            // Handle the case where published_at is empty
            dd('Published date is empty.');
        }

        // Si la commande n'existe pas, je retourne une erreur 404
        if (!$order) {
            abort(404);
        }

        // J'initialise un tableau vide qui contiendra les données de la commande
        $orderData = [];
        // J'initialise une variable qui contiendra la quantité totale de produits
        $totalQuantity = 0;

        // Je boucle sur les produits de la commande
        foreach ($order->items as $item) {
            // Je récupère le produit de la commande
            $product = Product::find($item->product_id); // Je récupère l'id du produit

            // Si le produit existe, je l'ajoute au tableau de données de la commande
            if ($product) {

                // J'ajoute les données du produit au tableau de données de la commande
                $orderData[] = [
                    'product_id' => $item->product_id, // Je récupère l'id du produit
                    'Product Name' => $product->name, // Je récupère le nom du produit
                    'Description' => $product->description, // Je récupère la description du produit
                    'Quantity' => $item->qty, // Je récupère la quantité du produit
                ];
                $totalQuantity += $item->qty; // Je calcule la quantité totale de produits
            }
        }

        // Je stocke la date de création de la commande formatée
        $formattedCreationDate = $order->getFormattedPublishedDate();

        // Je stocke la date de livraison de la commande formatée
        $formattedDeliveredDate = $order->getFormattedDeliveredDate();

        // Chemin du répertoire pour le PDF, avec un sous-dossier pour le mois et l'année
        $pdfDirectory = 'pdf/' . $year . '/' . 'rapport-livraison' . '/';
        $fullPdfDirectory = storage_path('app/public/' . $pdfDirectory);

        // Vérifiez si le répertoire existe, sinon, créez-le
        if (!file_exists($fullPdfDirectory)) {
            // 0755 est le mode par défaut pour les répertoires, il permet de créer des répertoires lisibles et accessibles en écriture pour tout le monde. True permet de créer les répertoires parents si nécessaire
            mkdir($fullPdfDirectory, 0755, true);
        }

        // Génération du PDF avec les données de la commande
        $pdf = PDF::loadView('pages.rapport.pdf.livraison', compact('order', 'orderData', 'formattedCreationDate', 'formattedDeliveredDate', 'totalQuantity'));

        // Enregistrement du PDF dans le dossier storage/app/public dans un sous-dossier nommé avec le mois et de l'année de la commande
        $pdfFileName = $order->id . '-' . $order->created_at->format('d-m-Y') . '-' . $order->customer->id . '.pdf';

        // Si le fichier existe déjà, on le supprime puis on le recrée pour éviter d'avoir des fichiers obsolètes ou pour les commandes modifiées.
        if (file_exists($fullPdfDirectory . $pdfFileName)) {
            unlink($fullPdfDirectory . $pdfFileName);
        }

        // Enregistrement du PDF dans le dossier storage/app/public dans un sous-dossier nommé avec le mois et de l'année de la commande
        $pdfPath = $fullPdfDirectory . $pdfFileName;

        $app_url = env('APP_URL');
        $storage = Storage::url($pdfDirectory . $pdfFileName);
        $url = $app_url . $storage;
        // dd($url);

        // Enregistrement du PDF dans la dossier storage/app/public
        $pdf->save($pdfPath);

        $pdf->stream($pdfFileName);

        // Mise à jour de l'URL dans la base de données
        $order->url = $url;
        $order->save(); // Enregistrez les modifications dans la base de données

        return [
            'pdfPath' => $pdfPath,
            'storage' => $storage,
            'url' => $url,
        ];
    }

    /**
     * Générer le PDF des commandes du mois.
     *
     * @param int $month Le mois pour lequel vous souhaitez générer le PDF.
     * @param int $year L'année pour laquelle vous souhaitez générer le PDF.
     * @return \Illuminate\Http\Response
     */
    public function ordersByMonth($year, $month)
    {
        // Récupérer les commandes du mois et de l'année spécifiés
        $orders = Order::with('customer', 'items')
            ->whereMonth('published_at', $month)
            ->whereYear('published_at', $year)
            ->get();

        // Si aucune commande n'est trouvée, retourner une erreur 404
        if ($orders->isEmpty()) {
            abort(404);
        }

        // Initialiser un tableau pour stocker les données des commandes
        $ordersData = [];
        // Initialiser une variable pour stocker la quantité totale de produits
        $totalQuantity = 0;

        // Boucler sur les commandes
        foreach ($orders as $order) {
            // Initialiser un tableau pour stocker les données de chaque commande
            $orderData = [];

            // Boucler sur les produits de la commande
            foreach ($order->items as $item) {
                // Récupérer le produit associé à l'élément de commande
                $product = OrderProduct::find($item->order_product_id);

                // Si le produit existe, ajouter ses données au tableau de données de la commande
                if ($product) {
                    $orderData[] = [
                        'Product ID' => $item->order_product_id,
                        'Product Name' => $product->name,
                        'Description' => $product->description,
                        'Quantity' => $item->qty,
                    ];
                    $totalQuantity += $item->qty;
                }
            }

            // Stocker les dates de création et de livraison formatées
            $formattedCreationDate = $order->getFormattedPublishedDate();
            $formattedDeliveredDate = $order->getFormattedDeliveredDate();

            // Ajouter les données de la commande au tableau de données des commandes
            $ordersData[] = [
                'Order ID' => $order->id,
                'Customer Name' => $order->customer->name,
                'Creation Date' => $formattedCreationDate,
                'Delivered Date' => $formattedDeliveredDate,
                'Order Items' => $orderData, // Tableau de données des produits de la commande
            ];
        }

        // Utilisez date() pour obtenir le nom du mois en français
        $monthName = $this->translateToFrench($month, 'month');

        // dd($ordersData, $totalQuantity, $monthName, $year);

        // Générer le PDF avec les données
        $pdf = PDF::loadView('pages.rapport.pdf.orders_by_month', [
            'monthName' => $monthName,
            'year' => $year,
            'ordersData' => $ordersData,
            'totalQuantity' => $totalQuantity,
        ]);

        return $pdf->stream('orders_by_month.pdf');
    }

    /**
     * Méthode pour traduire les mois ou d'autres éléments en français.
     * ex: 1 => janvier, 2 => février, etc.
     */
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

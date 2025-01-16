<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanyInfo;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeliveryNoteMail;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur pour gérer les commandes et les bons de livraison
 */
class OrderController extends Controller
{
    /**
     * Génère le bon de livraison PDF pour une commande
     *
     * @param Order $order La commande pour laquelle générer le bon de livraison
     * @return \Barryvdh\DomPDF\PDF Le PDF généré
     */
    public function generateDeliveryNote(Order $order)
    {
        Log::info("Début de génération du bon de livraison pour la commande #{$order->number}");

        // Chargement des données de la commande avec les relations
        $order = Order::with('customer', 'items.product')->findOrFail($order->id);
        $company = CompanyInfo::getDefault();
        $orderData = [];

        // Préparation des données des articles
        foreach ($order->items as $item) {
            $orderData[] = [
                'Product Name' => $item->product->name,
                'Notes' => $order->notes,
                'Weight' => $item->qty,
            ];
        }

        $totalWeight = array_sum(array_column($orderData, 'Weight'));
        Log::debug("Poids total de la commande: {$totalWeight}kg");

        // Génération du PDF
        $pdf = PDF::loadView('pages.rapport.pdf.bon-livraison', [
            'order' => $order,
            'orderData' => $orderData,
            'totalWeight' => $totalWeight,
            'company' => $company,
        ]);

        // Création du répertoire de stockage
        $year = Carbon::parse($order->created_at)->format('Y');
        $pdfDirectory = "pdf/{$year}/bons-livraison/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            Log::info("Création du répertoire: {$fullPdfDirectory}");
            mkdir($fullPdfDirectory, 0755, true);
        }

        // Sauvegarde du fichier PDF
        $pdfFileName = "BL-{$order->number}.pdf";
        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $pdf->save($pdfPath);
        Log::info("PDF sauvegardé: {$pdfPath}");

        $storageUrl = Storage::url("{$pdfDirectory}{$pdfFileName}");

        // Mise à jour des URLs dans la base de données
        $order->url = config('app.url') . $storageUrl;
        $order->delivery_note_url = $storageUrl;
        $order->save();
        Log::info("URLs mises à jour pour la commande #{$order->number}");

        // Envoi du mail avec le bon de livraison
        try {
            Mail::to('liana.jacques@aquaphoenix.fr')
                ->cc('jacques.steeven@gmail.com')
                ->send(new DeliveryNoteMail($order, $pdfPath));
            Log::info("Email envoyé avec succès pour la commande #{$order->number}");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de l'email pour la commande #{$order->number}: " . $e->getMessage());
        }

        return $pdf;
    }

    /**
     * Télécharge le bon de livraison d'une commande
     *
     * @param Order $order La commande dont on veut télécharger le bon de livraison
     * @return \Symfony\Component\HttpFoundation\StreamedResponse Le flux de téléchargement du PDF
     */
    public function downloadDeliveryNote(Order $order)
    {
        Log::info("Téléchargement du bon de livraison demandé pour la commande #{$order->number}");
        $pdf = $this->generateDeliveryNote($order);
        return $pdf->stream("BL-{$order->number}.pdf");
    }
}

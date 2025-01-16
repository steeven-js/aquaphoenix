<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur pour générer les PDF des livraisons mensuelles
 */
class MonthPdfController extends Controller
{
    /**
     * Génère un PDF des livraisons pour un mois donné
     *
     * @param string $month Le mois au format mm (01-12)
     * @param string $year L'année au format YYYY
     * @return \Illuminate\Http\Response Le fichier PDF à télécharger
     */
    public function generatePdf(string $month, string $year)
    {
        Log::info("Génération du PDF des livraisons pour $month/$year");

        // Récupère les commandes livrées du mois avec leurs relations
        $orders = Order::query()
            ->with(['customer', 'items.product'])
            ->whereYear('delivered_date', $year)
            ->whereMonth('delivered_date', $month)
            ->where('status', 'livré')
            ->orderBy('delivered_date')
            ->get();

        Log::info("Nombre de commandes trouvées: " . $orders->count());

        // Calcule le poids total des livraisons
        $totalWeight = $orders->sum(function ($order) {
            return $order->items->sum('qty');
        });

        Log::info("Poids total des livraisons: $totalWeight");

        // Formate le nom du mois en français
        $date = Carbon::createFromDate($year, $month, 1);
        $monthName = $date->locale('fr')->monthName;

        Log::info("Génération du PDF pour le mois de $monthName $year");

        // Génère le PDF avec la vue et les données
        $pdf = Pdf::loadView('pdf.month-deliveries', [
            'orders' => $orders,
            'month' => $monthName,
            'year' => $year,
            'totalWeight' => $totalWeight,
        ]);

        Log::info("PDF généré avec succès");

        // Retourne le PDF pour téléchargement
        return $pdf->download("livraisons-$monthName-$year.pdf");
    }
}

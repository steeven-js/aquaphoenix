<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Shop\Order;
use App\Mail\LivraisonMail;
use App\Models\OrderProduct;
use App\Models\Shop\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use App\Http\Controllers\PrintController;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\Shop\OrderResource;
use App\Notifications\ReportDeliveredNotification;

class OrderController extends Controller
{

    public function index()
    {
        //
    }

    /**
     * Envoyer le PDF de la commande par e-mail.
     */
    public function livraisonMail(Order $order)
    {
        // Je récupère la commande
        $order = Order::with('customer', 'items')->find($order->id);

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

        // Appelez la méthode livraison du PrintController pour générer le PDF
        $printController = new PrintController(); // Créez une instance du PrintController
        $pdfPath = $printController->livraison($order->id); // Appelez la méthode livraison

        // Configurez les données pour l'e-mail
        $mailData = [
            'order' => $order,
            'number' => $order->number,
            'pdfPath' => $pdfPath,
            'orderUrl' => route('order.print', $order->id), // Je récupère l'id de la commande
            'url' => $order->url,
            'formattedCreationDate' => $order->getFormattedPublishedDate(),
            'formattedDeliveredDate' => $order->getFormattedDeliveredDate(),
            'totalQuantity' => $totalQuantity, // Assurez-vous que cette variable est définie correctement
        ];

        $result = $printController->livraison($order->id); // Appelez la méthode du contrôleur livraison
        $pdfPath = $result['pdfPath']; // Je récupère le chemin du PDF
        $storage = $result['storage']; // Je récupère le chemin du stockage
        $url = $result['url']; // Je récupère l'url du PDF

        Mail::to('lianajacques18@gmail.com')
            ->cc(['liana.jacques@aquaphoenix.fr','jacques.steeven@gmail.com'])
            ->send(new LivraisonMail($mailData, $storage, $url));

        // Marquez la commande comme rapport envoyé avec succès
        $order->report_delivered = 1;
        $order->report_delivered_date = now();
        $order->save();

        $recipient = auth()->user();

        Notification::make()
            ->title('Mail envoyé avec succès le'.' '. $order->report_delivered_date)
            ->actions([
                Action::make('View')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase($recipient);

        // Redirigez avec un message de succès
        return redirect()->route('filament.admin.pages.dashboard');
    }
}

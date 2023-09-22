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

    public function livraisonMail(Order $order)
    {
        // Récupérer la commande
        $order = $this->getOrderWithCustomerAndItems($order->id);

        // Vérifier si published_at est vide
        if (empty($order->published_at)) {
            dd('La date de publication est vide.');
        }

        // Initialiser les données de la commande et la quantité totale
        $orderData = [];
        $totalQuantity = 0;

        // Parcourir les produits de la commande
        foreach ($order->items as $item) {
            $product = $this->getProduct($item->product_id);

            if ($product) {
                $orderData[] = $this->getProductData($item, $product);
                $totalQuantity += $item->qty;
            }
        }

        // Générer le PDF
        $pdfPath = $this->generatePDF($order->id);

        // Configurer les données pour l'e-mail
        $mailData = $this->prepareMailData($order, $pdfPath, $totalQuantity);

        // Envoyer l'e-mail
        $this->sendEmail($mailData);

        // Marquer la commande comme rapport envoyé avec succès
        $this->markOrderAsReportDelivered($order);

        // Envoyer la notification
        $this->sendNotification($order);

        // Rediriger avec un message de succès
        return redirect()->route('filament.admin.pages.dashboard');
    }

    // Méthodes auxiliaires

    private function getOrderWithCustomerAndItems($orderId)
    {
        return Order::with('customer', 'items')->find($orderId);
    }

    private function getProduct($productId)
    {
        return Product::find($productId);
    }

    private function getProductData($item, $product)
    {
        return [
            'product_id' => $item->product_id,
            'Product Name' => $product->name,
            'Description' => $product->description,
            'Quantity' => $item->qty,
        ];
    }

    private function generatePDF($orderId)
    {
        $printController = new PrintController();
        return $printController->livraison($orderId);
    }

    private function prepareMailData($order, $pdfPath, $totalQuantity)
    {
        return [
            'order' => $order,
            'number' => $order->number,
            'pdfPath' => $pdfPath,
            'orderUrl' => route('order.print', $order->id),
            'url' => $order->url,
            'formattedCreationDate' => $order->getFormattedPublishedDate(),
            'formattedDeliveredDate' => $order->getFormattedDeliveredDate(),
            'totalQuantity' => $totalQuantity,
        ];
    }

    private function sendEmail($mailData)
    {
        $result = $this->generatePDF($mailData['order']->id);
        $pdfPath = $result['pdfPath'];
        $storage = $result['storage'];
        $url = $result['url'];

        Mail::to('lianajacques18@gmail.com')
            ->cc(['liana.jacques@aquaphoenix.fr', 'jacques.steeven@gmail.com'])
            ->send(new LivraisonMail($mailData, $storage, $url));
    }


    private function markOrderAsReportDelivered($order)
    {
        $order->report_delivered = 1;
        $order->report_delivered_date = now();
        $order->save();
    }

    private function sendNotification($order)
    {
        $recipient = auth()->user();

        Notification::make()
            ->title('Mail envoyé avec succès le'.' '. $order->report_delivered_date)
            ->actions([
                Action::make('View')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase($recipient);
    }
}

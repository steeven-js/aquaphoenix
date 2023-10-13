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

}

<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Filament\Notifications\Notification;

class CheckOrderDeliveryStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Order::query()
            ->where('status', '!=', 'livré')
            ->whereNotNull('delivered_date')
            ->where('delivered_date', '<=', now())
            ->each(function (Order $order) {
                $order->status = 'livré';
                $order->save();

                Notification::make()
                    ->title('Commande marquée comme livrée')
                    ->body("La commande {$order->number} a été automatiquement marquée comme livrée.")
                    ->warning()
                    ->send();
            });
    }
}

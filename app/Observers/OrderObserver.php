<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Http\Controllers\MonthController;
use App\Notifications\OrderCreated;
use App\Notifications\OrderDelivered;
use Carbon\Carbon;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->delivered_date) {
            $date = Carbon::parse($order->delivered_date);
            MonthController::updateMonthStats($date->format('m'), $date->format('Y'));
        }

        // Notifier tous les administrateurs
        User::query()
            ->each(function (User $user) use ($order) {
                $user->notify(new OrderCreated($order));
            });
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('delivered_date') || $order->isDirty('status')) {
            if ($order->isDirty('delivered_date') && $order->getOriginal('delivered_date')) {
                $oldDate = Carbon::parse($order->getOriginal('delivered_date'));
                MonthController::updateMonthStats($oldDate->format('m'), $oldDate->format('Y'));
            }

            if ($order->delivered_date) {
                $newDate = Carbon::parse($order->delivered_date);
                MonthController::updateMonthStats($newDate->format('m'), $newDate->format('Y'));
            }
        }

        // Notification de livraison automatique
        if ($order->delivered_date && $order->delivered_date <= now() && $order->status !== 'livré') {
            $order->status = 'livré';
            $order->saveQuietly();

            User::query()
                ->each(function (User $user) use ($order) {
                    $user->notify(new OrderDelivered($order));
                });
        }
    }

    public function deleted(Order $order): void
    {
        if ($order->delivered_date) {
            $date = Carbon::parse($order->delivered_date);
            MonthController::updateMonthStats($date->format('m'), $date->format('Y'));
        }
    }
}

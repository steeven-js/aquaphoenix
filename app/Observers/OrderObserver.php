<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Http\Controllers\MonthController;
use App\Notifications\OrderCreated;
use App\Notifications\OrderDelivered;
use Carbon\Carbon;

/**
 * Observateur pour le modèle Order
 * Gère les événements du cycle de vie des commandes
 */
class OrderObserver
{
    /**
     * Gère l'événement de création d'une commande
     * Met à jour les statistiques mensuelles si une date de livraison est définie
     * Notifie tous les administrateurs de la création
     *
     * @param Order $order La commande créée
     */
    public function created(Order $order): void
    {
        // Mise à jour des stats si date de livraison définie
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

    /**
     * Gère l'événement de mise à jour d'une commande
     * Met à jour les statistiques mensuelles si la date de livraison change
     * Marque automatiquement comme livrée et notifie si la date est passée
     *
     * @param Order $order La commande mise à jour
     */
    public function updated(Order $order): void
    {
        // Gestion du changement de date de livraison ou statut
        if ($order->isDirty('delivered_date') || $order->isDirty('status')) {
            // Mise à jour des stats pour l'ancienne date
            if ($order->isDirty('delivered_date') && $order->getOriginal('delivered_date')) {
                $oldDate = Carbon::parse($order->getOriginal('delivered_date'));
                MonthController::updateMonthStats($oldDate->format('m'), $oldDate->format('Y'));
            }

            // Mise à jour des stats pour la nouvelle date
            if ($order->delivered_date) {
                $newDate = Carbon::parse($order->delivered_date);
                MonthController::updateMonthStats($newDate->format('m'), $newDate->format('Y'));
            }
        }

        // Notification de livraison automatique
        if ($order->delivered_date && $order->delivered_date <= now() && $order->status !== 'livré') {
            $order->status = 'livré';
            $order->saveQuietly();

            // Notifier tous les administrateurs
            User::query()
                ->each(function (User $user) use ($order) {
                    $user->notify(new OrderDelivered($order));
                });
        }
    }

    /**
     * Gère l'événement de suppression d'une commande
     * Met à jour les statistiques mensuelles si la commande était livrée
     *
     * @param Order $order La commande supprimée
     */
    public function deleted(Order $order): void
    {
        // Mise à jour des stats si la commande était livrée
        if ($order->delivered_date) {
            $date = Carbon::parse($order->delivered_date);
            MonthController::updateMonthStats($date->format('m'), $date->format('Y'));
        }
    }
}

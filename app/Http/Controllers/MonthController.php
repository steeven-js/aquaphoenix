<?php

namespace App\Http\Controllers;


use App\Models\Month;
use App\Models\Shop\Order;
use App\Models\OrderByMonth;
use Illuminate\Http\Request;

class MonthController extends Controller
{
    public function month()
    {
        // Obtention de l'année en cours à l'aide de Carbon
        $year = now()->year;

        $monthArray = []; // Initialisation d'un tableau pour stocker les mois de l'année

        // Boucle pour parcourir tous les mois de l'année en cours
        for ($i = 1; $i <= 12; $i++) {
            // Calcul de la date de début du mois en utilisant le numéro de mois et l'année
            $startOfMonth = now()->setYear($year)->setMonth($i)->startOfMonth();

            // Calcul de la date de fin du mois en utilisant le numéro de mois et l'année
            $endOfMonth = now()->setYear($year)->setMonth($i)->endOfMonth();

            // Appel de la méthode count pour obtenir le nombre d'orders pour ce mois
            $count = $this->count($year, $i);

            // Vérifiez si le mois existe déjà dans la base de données
            $month = Month::where('year', $year)->where('month_number', $i)->first();

            if ($month) {
                // Le mois existe déjà, mettez à jour les données
                $month->start_date = $startOfMonth;
                $month->end_date = $endOfMonth;
                $month->report_status = false;
                $month->count = $count;
                $month->save();
            } else {
                // Le mois n'existe pas, créez un nouvel enregistrement
                $month = new Month;
                $month->year = $year;
                $month->month = $startOfMonth->monthName;
                $month->month_number = $i;
                $month->start_date = $startOfMonth;
                $month->end_date = $endOfMonth;
                $month->report_status = false;
                $month->count = $count;
                $month->save();
            }

            $monthArray[] = $month;
        }

        // Supprimez les enregistrements de mois qui ne correspondent pas aux mois actuels
        $existingMonths = Month::where('year', $year)->whereNotIn('month_number', range(1, 12))->get();
        foreach ($existingMonths as $existingMonth) {
            $existingMonth->delete();
        }

        $this->fillOrdersByMonth();
    }

    // Méthode pour obtenir le nombre d'orders pour un mois donné
    public function count($year, $month)
    {
        // Obtention du nombre d'orders pour le mois et l'année spécifiés
        $count = Order::whereYear('published_at', $year)->whereMonth('published_at', $month)->count();

        return $count;
    }

    // Pour chaque order, remplir la table pivot orders_by_month
    public function fillOrdersByMonth()
    {
        // Obtention de l'année en cours à l'aide de Carbon
        $year = now()->year;

        // Boucle pour parcourir tous les mois de l'année en cours
        for ($i = 1; $i <= 12; $i++) {
            // Calcul de la date de début du mois en utilisant le numéro de mois et l'année
            $startOfMonth = now()->setYear($year)->setMonth($i)->startOfMonth();

            // Calcul de la date de fin du mois en utilisant le numéro de mois et l'année
            $endOfMonth = now()->setYear($year)->setMonth($i)->endOfMonth();

            // Obtention de tous les orders pour le mois et l'année spécifiés
            $orders = Order::whereYear('published_at', $year)->whereMonth('published_at', $i)->get();

            // Boucle pour parcourir tous les orders du mois
            foreach ($orders as $order) {
                // Vérifiez d'abord si l'enregistrement existe déjà dans la table orders_by_month
                $existingRecord = OrderByMonth::where('year', $year)
                    ->where('month', $startOfMonth->monthName)
                    ->where('order_id', $order->id)
                    ->where('month_id', $i)
                    ->first();

                if ($existingRecord) {
                    // L'enregistrement existe déjà, mettez à jour les données
                    $existingRecord->year = $year;
                    $existingRecord->month = $startOfMonth->monthName;
                    $existingRecord->order_id = $order->id;
                    $existingRecord->month_id = $i;
                    $existingRecord->save();
                } else {
                    // L'enregistrement n'existe pas, créez un nouvel enregistrement
                    $orderByMonth = new OrderByMonth;
                    $orderByMonth->year = $year;
                    $orderByMonth->month = $startOfMonth->monthName;
                    $orderByMonth->order_id = $order->id;
                    $orderByMonth->month_id = $i;
                    $orderByMonth->save();
                }
            }

            // Supprimer les enregistrements qui ne sont plus présents dans le mois actuel
            OrderByMonth::where('year', $year)
                ->where('month', $startOfMonth->monthName)
                ->where('month_id', $i)
                ->whereNotIn('order_id', $orders->pluck('id'))
                ->delete();
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MonthController;
use Illuminate\Support\Facades\Log;

class InitializeStats extends Command
{
    /**
     * Le nom et la signature de la commande console.
     *
     * @var string
     */
    protected $signature = 'stats:initialize';

    /**
     * La description de la commande console.
     *
     * @var string
     */
    protected $description = 'Initialise les statistiques mensuelles (exécution chaque minute)';

    /**
     * Exécute la commande console.
     */
    public function handle()
    {
        try {
            Log::channel('stats')->info('Démarrage de la commande stats:initialize');

            MonthController::initializeAllMonths();

            Log::channel('stats')->info('Initialisation terminée avec succès');
            return 0;
        } catch (\Exception $e) {
            Log::channel('stats')->error('Erreur: ' . $e->getMessage());
            return 1;
        }
    }
}

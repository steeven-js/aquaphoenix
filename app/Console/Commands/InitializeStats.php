<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MonthController;

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
    protected $description = 'Initialise les statistiques mensuelles';

    /**
     * Exécute la commande console.
     */
    public function handle()
    {
        Log::info('Démarrage de la commande stats:initialize');
        try {
            $this->info('Début de l\'initialisation des statistiques mensuelles...');
            Log::info('Début de l\'initialisation des statistiques mensuelles...');

            MonthController::initializeAllMonths();

            $this->info('Initialisation des statistiques terminée avec succès');
            Log::info('Initialisation des statistiques terminée avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'initialisation des statistiques: ' . $e->getMessage());
            $this->error('Une erreur est survenue: ' . $e->getMessage());
        }
    }
}

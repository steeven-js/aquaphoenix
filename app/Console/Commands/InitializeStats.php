<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        $this->info('Début de l\'initialisation des statistiques mensuelles...');
        MonthController::initializeAllMonths();
        $this->info('Initialisation des statistiques terminée avec succès');
    }
}

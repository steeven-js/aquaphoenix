<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\MonthController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('stats:initialize', function () {
    $this->info('Début de l\'initialisation des statistiques mensuelles...');
    MonthController::initializeAllMonths();
    $this->info('Initialisation des statistiques terminée avec succès');
})->purpose('Initialise les statistiques mensuelles')->everyFiveMinutes();

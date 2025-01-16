<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Artisan::command('stats:initialize', function () {
    $this->call('stats:initialize');
})->purpose('Initialise les statistiques mensuelles')->everyMinute();

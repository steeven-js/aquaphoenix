<?php

namespace App\Console\Commands;

use App\Models\Month;
use Illuminate\Console\Command;

class GenerateAllOrdersByMonthPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-all-orders-by-month-pdfs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Récupérer les mois ou il y a des commandes
        $months = Month::where('count', '>', 0)->get();

        foreach ($months as $month) {
            $this->info('Generating PDF for month ' . $month->id);
            // Appeler la méthode generatePdf du contrôleur
            app('App\Http\Controllers\OrderController')->generateAllOrdersByMonthPdfs($month);
        }
    }
}

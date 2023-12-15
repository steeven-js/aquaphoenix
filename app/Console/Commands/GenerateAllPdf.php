<?php

namespace App\Console\Commands;

use App\Models\Shop\Order;
use Illuminate\Console\Command;

class GenerateAllPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-all-pdf';

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
        $orders = Order::all();

        foreach ($orders as $order) {
            $this->info('Generating PDF for order ' . $order->id);
            // Appeler la méthode generatePdf du contrôleur
            app('App\Http\Controllers\OrderController')->generatePdf($order);
        }

        $this->info('PDFs generated for all orders.');
    }
}

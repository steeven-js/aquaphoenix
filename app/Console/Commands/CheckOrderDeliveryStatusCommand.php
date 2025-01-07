<?php

namespace App\Console\Commands;

use App\Jobs\CheckOrderDeliveryStatus;
use Illuminate\Console\Command;

class CheckOrderDeliveryStatusCommand extends Command
{
    protected $signature = 'orders:check-delivery-status';
    protected $description = 'Check and update order delivery status';

    public function handle()
    {
        CheckOrderDeliveryStatus::dispatch();
        $this->info('Order delivery status check has been queued.');
    }
}

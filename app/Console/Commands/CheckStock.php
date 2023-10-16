<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\product;

class CheckStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check stock quantity and send notifications if it is low.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {   
        $lowStockItems = product::where('quantity', '<', 50)->get();

        foreach ($lowStockItems as $item) {
            $this->info($item);
        }

        $this->info('Stock check completed.');

    }
}

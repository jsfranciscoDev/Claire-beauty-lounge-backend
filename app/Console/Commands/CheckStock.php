<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\product;
use App\Models\Notifications;

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
        $Notifications = Notifications::latest('created_at')->first();

        $mobile_number = '0'.$Notifications->phone_number;
        $product_details = [];
        $lowStockItems = product::where('quantity', '<', $Notifications->quantity)->get();

        foreach ($lowStockItems as $key => $item) {
            array_push($product_details, 'product name: '.$item->name);
            array_push($product_details, 'batch number: '.$item->batch_number);
            array_push($product_details, 'quantity: '.$item->quantity);
            array_push($product_details, 'price: '.$item->price);
        }

        $details_string = implode(", ", $product_details);

        if(!empty($product_details)){

            $ch = curl_init();
            $parameters = array(
                'apikey' => '01f7093eedd3bc546f9b256c301b01cf', 
                'number' => $mobile_number,
                'message' => 'The following products are currently is in low stock: '. $details_string,
                'sendername' => 'SEMAPHORE'
            );
            curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
            curl_setopt($ch, CURLOPT_POST, 1);
    
            //Send the parameters set above with the request
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    
            // Receive response from server
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
        }

        \Log::info('send notification');
    }
}

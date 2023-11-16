<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\product;
use Carbon\Carbon;
use App\Models\Notifications;
use Mail;
use App\Mail\replyEmail;

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
        $now = Carbon::now();

        $Notifications = Notifications::latest('created_at')->first();

        $mobile_number = '0'.$Notifications->phone_number;
        $email = $Notifications->email;
        $product_details = [];
        $lowStockItems = product::where('quantity', '<', $Notifications->quantity)->whereNull('deleted_at')->get();

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
                'sendername' => 'CLAIRE'
            );
            curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
            curl_setopt($ch, CURLOPT_POST, 1);
    
            //Send the parameters set above with the request
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    
            // Receive response from server
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);

            $mailData = [
                'title' => 'Claire Beauty Lounge Stock Update',
                'body' => 'The following products are currently is in low stock: '. $details_string,
            ];
          
            Mail::to($email)->send(new ReplyEmail($mailData));
    

        }


        $expireProducts = Product::whereYear('expiration_date', $now->year)
        // ->whereMonth('expiration_date', $now->month)
        ->whereDate('expiration_date', Carbon::now()->addDays(7)->toDateString())
        ->whereNull('deleted_at')
        ->get();

        foreach ($expireProducts as $key => $item) {
            array_push($product_details, 'product name: '.$item->name);
            array_push($product_details, 'batch number: '.$item->batch_number);
            array_push($product_details, 'expiration date: '.$item->expiration_date);
            array_push($product_details, 'price: '.$item->price);
        }

        $details_string = implode(", ", $product_details);

        
        if($expireProducts->isNotEmpty()){
           
            $ch = curl_init();
            $parameters = array(
                'apikey' => '01f7093eedd3bc546f9b256c301b01cf', 
                'number' => $mobile_number,
                'message' => 'The following products will expire soon : '. $details_string,
                'sendername' => 'CLAIRE'
            );
            curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
            curl_setopt($ch, CURLOPT_POST, 1);
    
            //Send the parameters set above with the request
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    
            // Receive response from server
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);

            $mailData = [
                'title' => 'Claire Beauty Lounge Stock Update',
                'body' => 'The following products will expire soon : '. $details_string,
            ];
          
            Mail::to($email)->send(new ReplyEmail($mailData));
        } 
        
    }
}

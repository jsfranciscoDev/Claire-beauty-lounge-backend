<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Otp;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\RequestOptions;
use Carbon\Carbon;

class SmsController extends Controller
{
    //
    public function sendSms(Request $request){
        $client = new Client([
            'base_uri' => "https://qyymgw.api.infobip.com/",
            'headers' => [
                'Authorization' => "App 9dd27810d14f0a083b6d34ac5c180389-9548592e-19ec-43b2-8932-06a91ebe8a17",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        $otp = '';
        for ($i = 0; $i < 6; $i++) {
            $otp .= rand(1, 6);
        }

        $response = $client->request(
            'POST',
            'sms/2/text/advanced',
            [
                RequestOptions::JSON => [
                    'messages' => [
                        [
                            'from' => 'Steven',
                            'destinations' => [
                                ['to' => "+639763386855"]
                            ],
                            'text' => "Your OTP for verification is: $otp. Please use this code to complete the verification process. Note: This OTP is valid for 3 minutes. Do not share it with anyone.",
                        ]
                    ]
                ],
            ]
        );

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        
        $user = Auth::user();

        $otpData = new Otp();
        $otpData->user_id = $user->id; 
        $otpData->otp = $otp; 
        $otpData->expiration = Carbon::now()->addMinutes(3); 
        $otpData->save();

        return response()->json([
            'otp_id' =>  $otpData->id,
        ]);
    }

    public function VerifyOtp(Request $request){
        \Log::info($request->all());
        $otp = Otp::where('id', $request->get('otp_id'))->first();
        \Log::info(json_encode($otp));
        \Log::info($otp->expiration);
        if ($otp) {
            $expirationTime = new Carbon($otp->expiration);
        
            if ($expirationTime->isPast()) {
                return response()->json([
                    'status' =>  'failed',
                    'message' =>  'The one-time password has expired. Please request a new OTP to proceed with the verification process.',
                    'title' => 'Verification Failed',
                ]);
            } else {
                \Log::info('hinde pa expired');
                if($otp->otp == $request->get('user_otp')){
                    return response()->json([
                        'status' =>  'verified',
                        'message' =>  'The appointment request has been submitted. Please await confirmation from the administrator. Thank you for your patience!',
                        'title' => 'Verification Success',
                    ]);
                }else{
                    return response()->json([
                        'status' =>  'failed',
                        'message' =>  'Incorrect one-time password!',
                        'title' => 'Verification Failed',
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' =>  'failed',
                'message' =>  'Invalid OTP',
                'title' => 'OTP Does not exist',
            ]);
        }
    }
    
}

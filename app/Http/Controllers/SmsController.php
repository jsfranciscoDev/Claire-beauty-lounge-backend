<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Otp;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\RequestOptions;
use Carbon\Carbon;
use App\Models\User;
use Mail;
use App\Mail\replyEmail;

class SmsController extends Controller
{
    //
    public function sendSms(Request $request){
       
        $mobile = null;
        $type = null;
        $user = Auth::user();
       
       if($request->contact){
            $mobile = $request->contact;
            $type = "registration";
       }

        $otp = '';
        for ($i = 0; $i < 6; $i++) {
            $otp .= rand(1, 6);
        }

        if($mobile){
            $ch = curl_init();
            $parameters = array(
                'apikey' => '01f7093eedd3bc546f9b256c301b01cf', 
                'number' => $mobile,
                'message' => 'Your OTP for verification is: '.  $otp . '. Please use this code to complete the verification process. Note: This OTP is valid for 10 minutes.',
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
        }
       
        $mailData = [
            'title' => 'Claire Beauty Lounge',
            'body' => 'Your OTP for verification is: ' . $otp . '. Please use this code to complete the verification process. Note: This OTP is valid for 10 minutes.',
        ];
        $recipientEmail = $request->email;
        Mail::to($recipientEmail)->send(new ReplyEmail($mailData));

        $otpData = new Otp();
        $otpData->otp = $otp; 
        $otpData->type = $type;
        $otpData->expiration = Carbon::now()->addMinutes(3); 
        $otpData->save();

       
  
      

        return response()->json([
            'otp_id' =>  $otpData->id,
        ]);
    }

    public function VerifyOtp(Request $request){
      
        $otp = Otp::where('id', $request->get('otp_id'))->first();
   
        if ($otp) {
            $expirationTime = new Carbon($otp->expiration);
        
            if ($expirationTime->isPast()) {
                return response()->json([
                    'status' =>  'failed',
                    'message' =>  'The one-time password has expired. Please request a new OTP to proceed with the verification process.',
                    'title' => 'Verification Failed',
                ]);
            } else {
               
                if($otp->otp == $request->get('user_otp')){
                    if($otp->type == 'appointment'){
                        return response()->json([
                            'status' =>  'verified',
                            'message' =>  'The appointment request has been submitted. Please await confirmation from the administrator. Thank you for your patience!',
                            'title' => 'Verification Success',
                        ]);
                    }else if($otp->type == 'registration'){
                        return response()->json([
                            'status' =>  'verified',
                            'message' =>  'Congratulations! Your registration is successful. You can now proceed to login.',
                            'title' => 'Verification Success',
                        ]);
                    }else if($otp->type == 'recovery'){
                        return response()->json([
                            'status' =>  'verified',
                            'message' =>  'Verified. You can now proceed change password.',
                            'title' => 'Verification Success',
                        ]);
                    }
                   
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

    public function getRecoveryOTP(Request $request){
      
        $data = User::find($request->get('user_id'));
        $mobile = null;
        $type = null;

        
        if($data->contact){
            $mobile = '0'.$data->contact;
            $type = "recovery";
        }

        $otp = '';

        for ($i = 0; $i < 6; $i++) {
            $otp .= rand(1, 6);
        }

        if($mobile){
            $ch = curl_init();
            $parameters = array(
                'apikey' => '01f7093eedd3bc546f9b256c301b01cf', 
                'number' => $mobile,
                'message' => ''.  $otp . ' is your Claire Beauty Lounge reset code. Please use this code to complete the Account Recovery process. Note: This OTP is valid for 3 minutes.',
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
        }

        $mailData = [
            'title' => 'Claire Beauty Lounge',
            'body' => 'Your OTP for verification is: ' . $otp . '. Please use this code to complete the Account Recovery process. Note: This OTP is valid for 10 minutes.',
        ];
        $recipientEmail =  $data->email;
        Mail::to($recipientEmail)->send(new ReplyEmail($mailData));
        
        //OTP time
        $otpData = new Otp();
        $otpData->otp = $otp; 
        $otpData->type = $type;
        $otpData->expiration = Carbon::now()->addMinutes(10); 
        $otpData->save();

        return response()->json([
            'otp_id' =>  $otpData->id,
        ]);
    }
    
}

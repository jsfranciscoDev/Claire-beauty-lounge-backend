<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Mail\replyEmail;

class MailController extends Controller
{
    //
    public function index(Request $request)
    {   
      
        $mailData = [
            'title' => 'Claire Beauty Lounge',
            'body' => $request->input('reply_msg'),
        ];
         
        Mail::to($request->input('recipeintemail'))->send(new replyEmail($mailData));

        return response()->json(['message' => 'Email Sent', 'status' => 'Success']);
    }
}

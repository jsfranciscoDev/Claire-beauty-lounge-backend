<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Mail\replyEmail;

class MailController extends Controller
{
    //
    public function index()
    {
        $mailData = [
            'title' => 'Mail from test.com',
            'body' => 'This is for testing email using smtp.'
        ];
         
        Mail::to('steven123francisco@gmail.com')->send(new replyEmail($mailData));
           
        dd("Email is sent successfully.");
    }
}

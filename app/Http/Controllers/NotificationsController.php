<?php

namespace App\Http\Controllers;
use App\Models\Notifications;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    //

    public function UpdateNotification(Request $request){
        \Log::info($request->all());

        $Notifications = new Notifications();
        $Notifications->quantity =  $request->get('quantity');
        $Notifications->phone_number = $request->get('phone_number'); 
        $Notifications->email = $request->get('email'); 
        $Notifications->save();

        return response()->json([
            'status' =>  'success',
            'message' =>  'The Notification settings Successfully Updated!',
            'title' => 'Update Successfully',
        ]);
    }

    public function NotificationData(Request $request){
        $Notifications = Notifications::latest('created_at')->first();
        $Notifications->phone_number =  '0'.$Notifications->phone_number;
        $Notifications->email = $Notifications->email;

        return response()->json([
            'notification' => $Notifications,
            'status' =>  'success',
            'message' =>  'The Notification settings Successfully Updated!',
            'title' => 'Update Successfully',
        ]);
    }
}

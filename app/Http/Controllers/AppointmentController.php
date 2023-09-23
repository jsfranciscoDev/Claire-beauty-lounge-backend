<?php

namespace App\Http\Controllers;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class AppointmentController extends Controller
{
    //
    public function createAppointment(Request $request){
      
        DB::beginTransaction();

        $date = $request->input('date');
        $time = $request->input('time');

        $date_time =  $date.' '.$time;
      

        try {
            $appointment = new Appointment();
            $appointment->service_type = $request->input('service_id'); 
            $appointment->user_id = $request->input('user_id');
            $appointment->date = $date_time; 
            $appointment->status = 1; 
            $appointment->save();

            DB::commit();

            return response()->json(['message' => 'Appointment Book Successfully!', 'status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Appointment', 'status' => 'failed', 'error' => $e->getMessage()]);
        }

    }

    public function getUserAppointment(){
        $user = Auth::user();

        $data = Appointment::getQuery()
        ->join('services','services.id','appointment.service_type')
        ->where('appointment.user_id', $user->id)
        ->latest('appointment.created_at')
        ->first();

        $response = [
            'appointment' => $data,
            'message' => 'success'
        ];

        return response($response, 200);
    }
}

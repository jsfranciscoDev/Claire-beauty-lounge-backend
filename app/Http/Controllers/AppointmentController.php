<?php

namespace App\Http\Controllers;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Services;
use App\Models\ServiceProducts;
use App\Models\product;

class AppointmentController extends Controller
{
    //
    public function createAppointment(Request $request){
        \Log::info($request->all());
        DB::beginTransaction();
        $user = Auth::user();
        $date = $request->input('date');
        $time = $request->input('time');

        $date_time =  $date.' '.$time;
      

        try {
            $appointment = new Appointment();
            $appointment->service_id = $request->input('service_id'); 
            $appointment->user_id = $user->id;
            $appointment->date = $date_time; 
            $appointment->status = 1; 
            $appointment->staff_id = $request->input('user_staff');
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
        ->join('services','services.id','appointment.service_id')
        ->join('appointment_status','appointment.status','appointment_status.id')
        ->where('appointment.user_id', $user->id)
        ->select(           
            'services.name as service',
            'appointment.id as appointment_id',
            'appointment.status',
            'appointment.date',
            'appointment_status.detail',
            \DB::raw('(SELECT name FROM users WHERE id = appointment.staff_id) as staff_name') // Subquery to get user name 
        )
        ->latest('appointment.created_at')
        ->first();
        

        $response = [
            'appointment' => $data,
            'message' => 'success'
        ];

        return response($response, 200);
    }

    public function getAllAppointments(){
       
        $data = Appointment::getQuery()
        ->join('users','users.id','appointment.user_id')
        ->join('services','services.id','appointment.service_id')
        ->join('appointment_status','appointment_status.id','appointment.status')
        ->select(
            'users.id',
            'users.name as name',
            'users.email',
            'users.contact',
            'services.name as service',
            'appointment.id as appointment_id',
            'appointment.status',
            'appointment.date',
            'appointment_status.detail',
            \DB::raw('(SELECT name FROM users WHERE id = appointment.staff_id) as staff_name')
        )
        // ->whereIn('appointment.id', function($query) {
        //     $query->select(DB::raw('MAX(id)'))
        //         ->from('appointment')
        //         ->groupBy('user_id');
        // })
        ->paginate(5);
    

        $response = [
            'appointment' => $data,
            'message' => 'success'
        ];

        return response($response, 200);
    }


    public function getStatusAppointments(Request $request){
       
        $data = Appointment::getQuery()
        ->join('users','users.id','appointment.user_id')
        ->join('services','services.id','appointment.service_id')
        ->join('appointment_status','appointment_status.id','appointment.status')
        ->select(
            'users.id',
            'users.name as name',
            'users.email',
            'users.contact',
            'services.name as service',
            'appointment.id as appointment_id',
            'appointment.status',
            'appointment.date',
            'appointment_status.detail',
            \DB::raw('(SELECT name FROM users WHERE id = appointment.staff_id) as staff_name')
        )
        ->where('appointment.status', $request->get('status'))
        // ->whereIn('appointment.id', function($query) {
        //     $query->select(DB::raw('MAX(id)'))
        //         ->from('appointment')
        //         ->groupBy('user_id');
        // })
        ->paginate(5);
    

        $response = [
            'appointment' => $data,
            'message' => 'success'
        ];

        return response($response, 200);
    }


    public function updateAppointment(Request $request){

        DB::beginTransaction();

        try {
            $appointment = Appointment::find($request->input('id'));
            \Log::info(json_encode($appointment));
            $appointment->status = $request->input('status');
            $appointment->save();
            DB::commit();


            $appointment_status = Appointment::find($request->input('id'));

            if($appointment_status->status == 5){
                $this->adjustProductQuantity( $appointment_status->id);
            }
            return response()->json(['message' => 'Appointment Updated Successfully!', 'status' => 'success']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Updating Appointment', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    public function adjustProductQuantity($id){
        $services = Services::where('id', $id)->first(); // Assuming you only need one service
        if($services) {
            $items = ServiceProducts::join('products','products.id','services_products.product_id')
                ->where('services_products.services_id', $id)
                ->whereNull('services_products.deleted_at')
                ->whereNull('products.deleted_at')
                ->select(
                    'products.id',
                    'products.name as name',
                    'products.price as price',
                    'services_products.quantity as quantity'
                )
                ->get();    
                
               if($items->isNotEmpty()){
                    foreach($items as $item){
                        foreach($items as $item){
                            DB::beginTransaction();
                            try {
                                product::where('id', $item->id)->decrement('quantity', $item->quantity);
                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollback();
                                // Handle the exception (e.g., log it or display an error message)
                            }
                        }
                    }
               }
        }
       
    }
}

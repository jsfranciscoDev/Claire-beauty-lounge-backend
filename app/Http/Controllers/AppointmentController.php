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
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    //
    public function createAppointment(Request $request)
    {
        
        $user = Auth::user();
        $date = $request->input('date');
        $time = $request->input('time');
        $date_time = $date . ' ' . $time;
    
        $validateIfReschedule = Appointment::where('user_id', $user->id)->first();
    
        if ($validateIfReschedule && $validateIfReschedule->status == 4) {
            $this->updateResched($request, $validateIfReschedule->id);
            return response()->json(['message' => 'Appointment Updated Successfully!', 'status' => 'success']);
        }
        else{
            DB::beginTransaction();
            try {
                $appointment = new Appointment();
                $appointment->service_id = $request->input('service_id');
                $appointment->user_id = $user->id;
                $appointment->date = $date_time;
                $appointment->status = 1;
                $appointment->staff_id = $request->input('user_staff');
                $appointment->save();
                DB::commit();
                return response()->json(['message' => 'Appointment Booked Successfully!', 'status' => 'success']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Error Creating Appointment', 'status' => 'failed', 'error' => $e->getMessage()]);
            }
        }
        
    }
    
    public function updateResched($request, $appointmentId)
    {
        
        $user = auth()->user();
        $date = $request->input('date');
        $time = $request->input('time');
        $date_time = $date . ' ' . $time;
    
        DB::beginTransaction();
        try {
            $appointment = Appointment::find($appointmentId);
            $appointment->remarks = null;
            $appointment->process_by = null;
            $appointment->user_id = $user->id;
            $appointment->date = $date_time;
            $appointment->staff_id = $request->input('user_staff');
            $appointment->status = 1;
            $appointment->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Updating Appointment', 'status' => 'failed', 'error' => $e->getMessage()]);
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
            'appointment.remarks',
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

    public function getAllAppointments(Request $request){
        \Log::info($request->all());
     
        $dateFilter = $request->input('datefilter');

        $formattedDateArray = [];

        // Check if 'datefilter' is set and is an array
        if (is_array($dateFilter)) {
            // Iterate through each date in the date range
            foreach ($dateFilter as $date) {
                // Parse the input date using Carbon
                $carbonDate = \Carbon\Carbon::parse($date);
                
                // Format the start of the date in the desired format
                $formattedStartDate = $carbonDate->format('Y-m-d 00:00:00');
                
                // Format the end of the date in the desired format (end of day)
                $formattedEndDate = $carbonDate->endOfDay()->format('Y-m-d 23:59:59');
                
                // Store the formatted dates in a new array
                $formattedDateArray[] = [
                    'date_from' => $formattedStartDate,
                    'date_to' => $formattedEndDate,
                ];
            }
        }
        
        \Log::info($formattedDateArray);

        $data = Appointment::getQuery()
        ->join('users','users.id','appointment.user_id')
        ->join('services','services.id','appointment.service_id')
        ->join('appointment_status','appointment_status.id','appointment.status')
        ->leftJoin('user_roles as process_by_role', 'process_by_role.id', 'appointment.process_by')
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
            \DB::raw('(SELECT name FROM users WHERE id = appointment.staff_id) as staff_name'),
            \DB::raw('(SELECT name FROM users WHERE id = appointment.process_by) as process_by'),
            'process_by_role.role as process_by_role'
        )
        ->when($formattedDateArray, function ($query) use ($formattedDateArray) {
            // Add a search filter based on the service name
            $query->whereBetween('appointment.date',  [$formattedDateArray['date_from'],$formattedDateArray['date_to']]);
        })
        ->when($request->has('search'), function ($query) use ($request) {
            $searchTerm = $request->input('search');
            // Add a search filter based on the service name
            $query->where('users.name', 'like', '%' . $searchTerm . '%');
        })
        // ->whereIn('appointment.id', function($query) {
        //     $query->select(DB::raw('MAX(id)'))
        //         ->from('appointment')
        //         ->groupBy('user_id');
        // })
        ->orderBy('appointment.created_at', 'desc')
        ->paginate(5);
    

        $response = [
            'appointment' => $data,
            'message' => 'success'
        ];

        return response($response, 200);
    }


    public function getStatusAppointments(Request $request){
        $dateFilter = $request->input('date');

        $formattedDateArray = [];

        // Check if 'datefilter' is set and is an array
        if (is_array($dateFilter)) {
            // Iterate through each date in the date range
            foreach ($dateFilter as $date) {
                // Parse the input date using Carbon
                $carbonDate = \Carbon\Carbon::parse($date);
                
                // Format the start of the date in the desired format
                $formattedStartDate = $carbonDate->format('Y-m-d 00:00:00');
                
                // Format the end of the date in the desired format (end of day)
                $formattedEndDate = $carbonDate->endOfDay()->format('Y-m-d 23:59:59');
                
                // Store the formatted dates in a new array
                $formattedDateArray[] = [
                    'date_from' => $formattedStartDate,
                    'date_to' => $formattedEndDate,
                ];
            }
        }
        
        \Log::info($formattedDateArray);
        

        $data = Appointment::getQuery()
        ->join('users','users.id','appointment.user_id')
        ->join('services','services.id','appointment.service_id')
        ->join('appointment_status','appointment_status.id','appointment.status')
        ->leftJoin('user_roles as process_by_role', 'process_by_role.id', 'appointment.process_by')
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
            \DB::raw('(SELECT name FROM users WHERE id = appointment.staff_id) as staff_name'),
            \DB::raw('(SELECT name FROM users WHERE id = appointment.process_by) as process_by'),
            'process_by_role.role as process_by_role'
        )
        ->when($formattedDateArray, function ($query) use ($formattedDateArray) {
            // Add a search filter based on the service name
            $query->whereBetween('appointment.date',  [$formattedDateArray['date_from'],$formattedDateArray['date_to']]);
        })
        ->when($request->has('search'), function ($query) use ($request) {
            $searchTerm = $request->input('search');
            // Add a search filter based on the service name
            $query->where('users.name', 'like', '%' . $searchTerm . '%');
        })
        ->where('appointment.status', $request->get('status'))
        // ->whereIn('appointment.id', function($query) {
        //     $query->select(DB::raw('MAX(id)'))
        //         ->from('appointment')
        //         ->groupBy('user_id');
        // })
        ->orderBy('appointment.date', 'asc')
        ->paginate(5);
    

        $response = [
            'appointment' => $data,
            'message' => 'success'
        ];

        return response($response, 200);
    }


    public function updateAppointment(Request $request){
       
        DB::beginTransaction();
        $user = auth()->user();
        try {
            $appointment = Appointment::find($request->input('id'));
            $appointment->status = $request->input('status');
            $appointment->remarks = null;
            $appointment->process_by = $user->id;
            $appointment->save();
            DB::commit();

            $appointment = Appointment::find($request->input('id'));

            if($appointment->status == 5){
                $this->adjustProductQuantity( $appointment->id);
            }else if($appointment->status == 2 || $appointment->status == 3){
                $this->notifyuser($appointment);
            }else if($appointment->status == 4){
                $appointment->remarks = $request->input('remarks');
                $this->notifyuser($appointment);
            }
            
            $appointment->save();
            DB::commit();
            

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

    public function notifyuser($appointment){

        $message = null;

        $user = User::find($appointment->user_id);
        $mobile = '0'.$user->contact;
        
        $date = $appointment->date;
        $carbon_date = Carbon::parse($date);
        $formattedDate = $carbon_date->format('F jS Y, g:i:s A');

        if($appointment->status == 3){
            $message = 'Your Appointment at Claire Beauty Lounge on '.  $formattedDate .' has been approved! Please make sure to arrive a little earlier than the scheduled time.';
        }else if($appointment->status == 2){
            $message = 'Your Appointment at Claire Beauty Lounge on '.  $formattedDate .' has been cancelled! ' .$appointment->remarks;
        }else if($appointment->status == 4){
            $message = 'Your Appointment at Claire Beauty Lounge on '.  $formattedDate .' has been reschedule! Please update your appointment at your most convenient available time' .$appointment->remarks;
        }

        if($mobile){
            $ch = curl_init();
            $parameters = array(
                'apikey' => '01f7093eedd3bc546f9b256c301b01cf', 
                'number' => $mobile,
                'message' => $message,
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
       
    }

    public function getScheduledAppointment(Request $request){
        \Log::info($request->input('date'));
        $date = Carbon::parse($request->input('date'));
        // Format the date to display only year, month, and date
        $formattedDate = $date->format('Y-m-d');

        \Log::info($formattedDate);

        $data = Appointment::getQuery()
        ->join('users','users.id','appointment.staff_id')
        ->join('services','services.id','appointment.service_id')
        ->where('appointment.status', 3)
        ->whereDate('date', $formattedDate)
        ->orderBy('appointment.date', 'asc')
        ->select(
            'users.name',
            'appointment.date',
            'services.estimated_hours'
        )
        ->paginate(5);
    
        $response = [
            'appointment' => $data,
            'message' => 'success'
        ];

        return response($response, 200);
        
    }

}

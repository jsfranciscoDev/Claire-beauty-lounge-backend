<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\UserProfile;
use App\Models\DailyTimeinRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffController extends Controller
{
    //

    public function createStaff(Request $request){

        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'contact' => 'required|numeric|digits:11',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'contact' => $fields['contact'],
            'role_id' => 2,
        ]);

     
        $user_profile = new UserProfile();
        $user_profile->user_id =  $user->id;
        $user_profile->path = 'storage/user/profile.png'; 
        $user_profile->save();

        $response = [
            'user' => $user,
            'message' => 'success'
        ];

        return response($response, 200);
        
    }

    public function getUserStaff(){
        
        $user = User::getQuery()
        ->join('user_roles','users.role_id','user_roles.id')
        ->where('user_roles.id', 2)
        ->select(
            'users.id as id',
            'users.name',
            'users.email',
            'user_roles.role',
            'users.contact'
        )
        ->paginate(10);
       
        $response = [
            'user' => $user,
            'message' => 'success'
        ];

        return response($response, 201);

    }

    public function removeStaff($id){
        $user = User::find($id);
        if($user){
            $user->delete();
            $response = [
                'message' => 'success'
            ];
            return response($response, 201);
        } else {
            $response = [
                'message' => 'delete failed!'
            ];
            return response($response, 404);
          
        }
    }

    public function getStaffDetails(){

        $user = User::getQuery()
        ->join('user_roles','users.role_id','user_roles.id')
        ->join('user_profile','users.id','user_profile.user_id')
        ->where('user_roles.id', 2)
        ->select(
            'users.id as id',
            'users.name',
            'users.email',
            'user_roles.role',
            'users.contact',
            'users.bio',
            'users.expertise',
            'user_profile.path'
        )->get();
        
        $response = [
            'user' => $user,
            'message' => 'success'
        ];

        return response($response, 201);
    }
    
    public function timeIn(Request $request){
   
        $currentDate = \Carbon\Carbon::now();

  
        try {
            DB::beginTransaction();
            
            if ($currentDate->format('Y-m-d') == date('Y-m-d', strtotime($request->input('date')))) {
                if ($request->input('action') == 'time_in') {
                    $user_record = new DailyTimeinRecord();
                    $user_record->user_id = $request->input('user_id');
                    $user_record->time_in = date('H:i:s', strtotime($request->input('time')));
                    $user_record->date = date('Y-m-d', strtotime($request->input('date')));
                    $user_record->save();
                    DB::commit();
                    return response()->json(['message' => 'Time in Successfully!', 'status' => 'success','action' => 'time_in']);
                } else if ($request->input('action') == 'time_out') {
                    $user_record = DailyTimeinRecord::where('user_id', $request->input('user_id'))
                        ->where('date', $currentDate->format('Y-m-d'))
                        ->first();
            
                    if ($user_record) {
                        $user_record->time_out = date('H:i:s', strtotime($request->input('time')));
                        $user_record->save();
                        DB::commit();
                        return response()->json(['message' => 'Time Out Successfully!', 'status' => 'success', 'action' => 'time_out']);
                    } else {
                        return response()->json(['message' => 'User record not found!', 'status' => 'failed']);
                    }
                } else {
                    return response()->json(['message' => 'Something Went Wrong!', 'status' => 'failed']);
                }
            } else {
                return response()->json(['message' => 'Invalid date!', 'status' => 'failed']);
            }
            
        
           
    
        } catch (\Exception $e) {
            // If an exception occurs, rollback the transaction
            DB::rollBack();
    
            // Handle the exception, log it, or return an error response
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Please Check your connection and time', 'status' => 'failed']);
        }
    }   

    public function getUserRecords(){
        $user = Auth::user();
        $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
        $user_action = DailyTimeinRecord::where('user_id', $user->id)->where('date', $currentDate)->get();
        if ($user_action->isEmpty()) {
            return response()->json(['action' => 'time_in']);
        } else{
            return response()->json(['action' => 'time_out']);
        }
    }

    public function getUserDTR(Request $request){
       
        $user = Auth::user();

        $currentDate = Carbon::now();
        $currentMonth = $currentDate->format('m');
       
        if($request->input('months')){
            $inputMonth = $request->input('months');
            $carbonDate = Carbon::createFromFormat('F', $inputMonth); // Assuming 'months' is in 'F' format (e.g., 'May')
            $currentMonth = $carbonDate->format('m');
        }

        if($request->input('user_id')){
            $user_records = DailyTimeinRecord::where('user_id', $request->input('user_id'))->whereMonth('date',  $currentMonth)->get();
        }else{
            $user_records = DailyTimeinRecord::where('user_id', $user->id)->whereMonth('date',  $currentMonth)->get();
        }
        


        $response = [
            'user_records' => $user_records
        ];

        return response($response, 201);
    }

    public function getUserDropdown(){

        $user_dropdown = User::select('name','id')->where('role_id', 2)->get();

        $response = [
            'user_dropdown' => $user_dropdown
        ];

        return response($response, 201);

    }
}

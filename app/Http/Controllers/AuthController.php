<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\UserProfile;

class AuthController extends Controller
{
    //
    public function register(Request $request){
       
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'contact' => 'required|numeric|digits:11',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'contact' => $fields['contact'],
            'password' => bcrypt($fields['password']),
            'role_id' =>$request['role_id']
        ]);

        $user_profile = new UserProfile();
        $user_profile->user_id =  $user->id;
        if ($imagePathConfig === 'LOCAL') {
            $image_path = 'storage/user/';
        } else {
            // when hosted should add public/
            $image_path = 'storage/app/public/user/';
        }
        $user_profile->path = $image_path.'profile.png'; 
        $user_profile->save();

        // $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
            'message' => 'success'
        ];

        return response($response, 201);
    }

    public function login(Request $request){
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response([
                'message' => 'Invalid Creds!'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;
        $role =  DB::table('user_roles')->where('id' , $user->role_id)->value('role');
        $staff_role =  DB::table('staff_roles')->where('id' , $user->staff_role)->value('role');

        $response = [
            'user' => $user,
            'token' => $token,
            'message' => 'success',
            'role' => $role,
            'staff_role' => $staff_role
        ];

        return response($response, 201);
    }

    public function logout(Request $request) {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged Out!'
        ];
    }

    public function changePassword(Request $request){
      

        $user = auth()->user();

        if($request->payload['new_password'] === $request->payload['password_confirmation']){
            if (!Hash::check($request->payload['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect',
                    'status' => 'failed'
                ]);
               
            }

            $user->password = bcrypt($request->payload['new_password']);
            $user->save();

           
            return response(['message' => 'Password changed successfully', 'status' => 'success'], 200);
        }else{
            return response()->json([
                'message' => 'Password Missmatch',
                'status' => 'failed'
            ]);
          
        }
    
    }

    public function roles(){
        $user = auth()->user();
        $role =  DB::table('user_roles')->where('id' , $user->role_id)->value('role');
        return $role;
    }

    public function recoverAccount(Request $request){
 

        $data = User::where('email', $request->get('user_email'))->first();

        if(!is_null($data)){

            $contact = $data->contact;

            if(!$contact){
                return response()->json([
                    'message' => 'Account lacks a recovery contact Number. Please contact the administrator.',
                    'status' => 'failed',
                ]);
            }
         
            $contactNumber = '0' . $contact;

            // Get the length of the string
            $length = strlen($contactNumber);

            // Calculate the number of middle digits to replace with asterisks
            $numAsterisks = $length - 8;

            // Create a string of asterisks
            $asterisks = str_repeat('*', $numAsterisks);

            // Replace the middle digits with asterisks
            $contactNumber = substr_replace($contactNumber, $asterisks, 4, -3);
         
            return response()->json([
                'message' => 'Verify your account!',
                'status' => 'success',
                'contact' =>  $contactNumber,
                'user_id' => $data->id,
            ]);

        }else{
            return response()->json([
                'message' => 'Email does not Exist!',
                'status' => 'failed'
            ]);
        }
       
    }

    public function recoveryChangePassword(Request $request){

        $user =  User::find($request->payload['user_id']);
        if($request->payload['password'] === $request->payload['confirm_password']){
           
            $user->password = bcrypt($request->payload['password']);
            $user->save();

            return response(['message' => 'Password changed successfully', 'status' => 'success'], 200);
        }else{
            return response()->json([
                'message' => 'Password Missmatch',
                'status' => 'failed'
            ]);
          
        }
    }
}

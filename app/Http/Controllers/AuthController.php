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
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role_id' =>$request['role_id']
        ]);

        $user_profile = new UserProfile();
        $user_profile->user_id =  $user->id;
        $user_profile->path = 'storage/user/profile.png'; 
        $user_profile->save();

        $token = $user->createToken('myapptoken')->plainTextToken;

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

        $response = [
            'user' => $user,
            'token' => $token,
            'message' => 'success',
            'role' => $role
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
}

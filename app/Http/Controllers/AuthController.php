<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

    public function roles(){
        $user = auth()->user();
        $role =  DB::table('user_roles')->where('id' , $user->role_id)->value('role');
        return $role;
    }
}

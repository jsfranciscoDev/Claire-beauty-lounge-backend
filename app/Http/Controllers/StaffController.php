<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\UserProfile;

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
    
}

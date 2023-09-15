<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //
    public function getUser(){
        $user = Auth::user();
        $user_profile =  $user->profile->path;
    
        return response()->json([
            'user' => $user,
            'profile' => $user_profile
        ]);
    }

    public function updateUser(Request $request){
        DB::beginTransaction();
        \Log::info($request->all());
        try {
            $user = User::find($request->input('id'));
            if ($user) {
                $user->name = $request->input('name');
                $user->email = $request->input('email');
                $user->contact = $request->input('contact');
                $user->expertise =  $request->input('expertise');
                $user->bio = $request->input('bio');
                $user->save();
                DB::commit(); 
                return response()->json([
                    'message' => 'Update Successfully!',
                    'status' => 'success'
                ]);
                
            } else {
                \Log::error('User not found');
            }

        } catch (\Exception $e) {
            DB::rollback(); // Rollback the transaction in case of an exception
            \Log::error('Error updating user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Somethin went wrong!',
                'status' => 'failed'
            ]);
        }
    }
}

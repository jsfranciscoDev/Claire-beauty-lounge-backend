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
}

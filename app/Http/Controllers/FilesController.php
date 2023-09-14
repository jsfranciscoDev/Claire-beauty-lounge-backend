<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\UserProfile;

class FilesController extends Controller
{
    //
    function uploadPhoto(Request $request)
    {   
        $user = auth()->user();
    
        if(!$request->file){
            return [
                'status' => false,
                'path' => null,
                'message' => 'Please select file.',
            ];
        }

        $base64_image = $request->file;

        $image_64 = $request->file; //your base64 encoded data

        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

        $replace = substr($image_64, 0, strpos($image_64, ',')+1); 

        $image = str_replace($replace, '', $image_64); 

        $image = str_replace(' ', '+', $image); 

        $imageName =  $user->name.'.'.$extension;

        Storage::disk('public')->put('user/' . $imageName, base64_decode($image));

        $image_path = 'storage/user/'. $imageName;

        $user_profile = new UserProfile();
        $user_profile = UserProfile::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'path' => $image_path,
        ]);
   
        if(!is_null($user_profile->path)){
            return response()->json([
                'status' => 'success',
                'path' =>  $image_path,
            ]);
        }else{
            return [
                'status' => 'Error',
                'message' => 'Upload Failed!',
            ];
        }
       

    }
}

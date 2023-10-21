<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\UserProfile;
use Illuminate\Support\Carbon;

class FilesController extends Controller
{
    //
    
    function uploadPhoto(Request $request)
    {   
        $user = auth()->user();
        $timestamp = Carbon::now()->timestamp;
      
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

        $imageName = Str::random(10).'-'.$timestamp.'.'.$extension;

        Storage::disk('public')->put('user/' . $imageName, base64_decode($image));

        \Log::info( env('IMAGE_PATH'));
        if( env('IMAGE_PATH') === 'LOCAL'){
            $image_path = 'storage/user/'. $imageName;
        }else{
            // when hosted should add public/
            $image_path = 'storage/app/public/user/'. $imageName;
        }

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

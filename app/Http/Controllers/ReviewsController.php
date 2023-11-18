<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Reviews;

class ReviewsController extends Controller
{
    //
    public function storeUserFeedback(Request $request){

        $user = auth()->user();
        // $timestamp = Carbon::now()->timestamp;
        
        // $base64_image = $request->payload['image'];

        // $image_64 = $request->payload['image']; //your base64 encoded data

        // $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

        // $replace = substr($image_64, 0, strpos($image_64, ',')+1); 

        // $image = str_replace($replace, '', $image_64); 

        // $image = str_replace(' ', '+', $image); 

        // $imageName = Str::random(10).'-'.$timestamp.'.'.$extension;
        // Storage::disk('public')->put('reviews/' . $imageName, base64_decode($image));
        
        // $imagePathConfig = config('imagepath.image_path');

        // if ($imagePathConfig === 'LOCAL') {
        //     $image_path = 'storage/reviews/' . $imageName;
           
        // } else {
        //     // when hosted should add public/
        //     $image_path = 'storage/app/public/reviews/' . $imageName;
            
        // }

        $reviews = new Reviews();
        $reviews->user_id = $user->id;
        $reviews->star_rating = $request->payload['start_rating'];
        $reviews->feedback = $request->payload['comment'];
        // $reviews->image_path = $image_path;
        $reviews->save();

        $appointment = Appointment::where('user_id',  $user->id)->where('review', 0)->first();
        $appointment->review = 1;
        $appointment->save();

        
        return response()->json([
            'status' => 'success',
            'message' => 'Feedback Successfully sent!'
        ]);
        
        
    }   

    public function getallReviews(Request $request){
        $data = Reviews::getQuery()
        ->join('users','users.id','reviews.user_id')
        ->leftjoin('user_profile', 'user_profile.user_id','users.id')
        ->select(
            'users.name as name',
            'user_profile.path as profile',
            'reviews.feedback as feedback',
            'reviews.star_rating',
            'reviews.image_path as review_image_path',
            'reviews.created_at',
        )->paginate(5);

        return response()->json([
           'reviews' =>  $data,
        ]);
        
        return response($response, 200);
    }
}

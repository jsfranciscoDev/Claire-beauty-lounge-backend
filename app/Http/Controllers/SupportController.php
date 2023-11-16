<?php

namespace App\Http\Controllers;

use App\Models\Support;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function SendSupport(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string',
            'message' => 'required|string',
        ]);

        $Support = Support::create([
            'user_id' => auth()->id(),
            'name' => $fields['name'],
            'email' => $fields['email'],
            'message'=> $fields['message'],
        ]);
        
        $response = [
            'support' => $Support,
            'message' => 'success'
        ];

        return response($response, 200);
    }

    public function fetchAllSupport(Request $request){
        $support = Support::getQuery()->paginate(5);
        
        $response = [
            'support' => $support,
            'message' => 'success'
        ];

        return response($response, 201);
    }
}

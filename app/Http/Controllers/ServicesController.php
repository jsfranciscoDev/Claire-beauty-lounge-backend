<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Services;
use Illuminate\Support\Facades\DB;

class ServicesController extends Controller
{
    public function createServices(Request $request) {
        DB::beginTransaction();

        try {
            $services = new Services();
            $services->name = $request->input('name');
            $services->type = $request->input('Type'); // Assuming 'type' is a valid column in your table
            $services->price = $request->input('price'); // Assuming 'price' is a valid column in your table
            $services->details = $request->input('details'); // Assuming 'details' is a valid column in your table
            $services->save();

            DB::commit();

            return response()->json(['message' => 'Services Created Successfully!', 'status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    public function getServices(){

        $services = Services::getQuery()->whereNull('deleted_at')->paginate(10);
       
        $response = [
            'services' => $services,
            'message' => 'success'
        ];

        return response($response, 201);
    }

    public function removeSevice($id){
        $services = Services::find($id);
        if($services){
            $services->delete();
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
}

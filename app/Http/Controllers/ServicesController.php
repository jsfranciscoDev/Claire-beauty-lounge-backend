<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Services;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\DB;
use App\Models\product;
use App\Models\ServiceProducts;
use Illuminate\Support\Facades\Auth;

class ServicesController extends Controller
{
    public function createServices(Request $request) {
        DB::beginTransaction();
        $user = auth()->user();
        try {
            $services = new Services();
            $services->name = $request->input('name');
            $services->service_category = $request->input('service_category'); // Assuming 'type' is a valid column in your table
            $services->price = $request->input('price');
            $services->estimated_hours = $request->input('estimated_hours'); // Assuming 'price' is a valid column in your table
            $services->details = $request->input('details'); // Assuming 'details' is a valid column in your table
            $services->user_id = $user->id;
            $services->save();

            DB::commit();

            return response()->json(['message' => 'Services Created Successfully!', 'status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    public function getServices(Request $request){
        $services = Services::getQuery()
        ->join('service_category', 'service_category.id', 'services.service_category')
        ->join('users','users.id','services.user_id')
        ->leftJoin('user_roles','user_roles.id','users.role_id')
        ->whereNull('services.deleted_at')
        ->select(
            'services.*',
            'service_category.name as category',
            'users.name as username',
            'user_roles.role as role',
        )
        ->when($request->has('search'), function ($query) use ($request) {
            $searchTerm = $request->input('search');
            // Add a search filter based on the service name
            $query->where('services.name', 'like', '%' . $searchTerm . '%');
        })
        ->paginate(3);

        $services->each(function($service) {
         
           $items = ServiceProducts::getQuery()
           ->join('products','products.id','services_products.product_id')
           ->where('services_products.services_id',$service->id)
           ->whereNull('services_products.deleted_at')
           ->whereNull('products.deleted_at')
           ->select(
            'products.name as name',
            'products.price as price',
            'services_products.quantity as quantity',
           )
           ->get();

           $total_item_price = 0;

           foreach($items as $item){
          
            $total_item_price += $item->price * $item->quantity; // Accumulate total
           
           }
      
           $service->products = $items;
           $service->total_product_price = $total_item_price;
         
           
        });
        
      

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

    public function updateServices(Request $request){
        DB::beginTransaction();
        $user = auth()->user();
        try {
            $services = Services::find( $request->input('id'));
            $services->name = $request->input('name');
            $services->service_category = $request->input('service_category'); // Assuming 'type' is a valid column in your table
            $services->price = $request->input('price');
            $services->estimated_hours = $request->input('estimated_hours'); // Assuming 'price' is a valid column in your table
            $services->details = $request->input('details');// Assuming 'details' is a valid column in your table
            $services->user_id =  $user->id;
            $services->save();

            DB::commit();

            return response()->json(['message' => 'Services Updated Successfully!', 'status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    public function getServicesDropdown(){
        $data = Services::select('id','name','price')->get();
        return $data;
    }   

    public function getProductsDropDown(){
        $data = product::select('id','name')->get();
        return $data;
    }

    public function createServiceItems(Request $request){
    
        $items = $request->get('service_items');

        foreach($items as $item){
           
            $exist = ServiceProducts::where('product_id', $item['product_id'])->where('services_id', $request->input('service_id'))->get();
        
            if(!$exist->isEmpty()){
                return response()->json(['message' => 'Some Products already added to this services! You must update the Quantity.', 'status' => 'failed']);
            }

            $services_item = new ServiceProducts();
            $services_item->product_id = $item['product_id'];
            $services_item->services_id = $request->input('service_id');
            $services_item->quantity = $item['quantity'];
            $services_item->save();
        }

        return response()->json(['message' => 'Services Product Added Successfully!', 'status' => 'success']);
       
    }

    public function removeSeviceItems(Request $request){

        $servicesProducts = ServiceProducts::where('services_id', $request->id)->get();
       
        if($servicesProducts->isNotEmpty()){ // Check if the collection is not empty
            ServiceProducts::where('services_id', $request->id)->delete(); // Delete all matching records
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

    public function createServiceCategory(Request $request) {
        DB::beginTransaction();
        $user = auth()->user();
        try {
            $services = new ServiceCategory();
            $services->name = $request->input('name');
            $services->user_id = $user->id;
            $services->save();

            DB::commit();

            return response()->json(['message' => 'Services Category Created Successfully!', 'status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services Category', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    public function getServiceCategory(Request $request){

        $services = ServiceCategory::getQuery()
        ->join('users','users.id','service_category.user_id')
        ->leftJoin('user_roles','user_roles.id','users.role_id')
        ->select(
            'service_category.id',
            'service_category.name as name',
            'users.name as username',
            'user_roles.role as role',
        )
        ->when($request->has('search'), function ($query) use ($request) {
            $searchTerm = $request->input('search');
            // Add a search filter based on the service name
            $query->where('service_category.name', 'like', '%' . $searchTerm . '%');
        })
        ->paginate(5);

        $response = [
            'services' => $services,
            'message' => 'success'
        ];

        return response($response, 201);
    }

    public function removeSeviceCategory($id){
        $services = ServiceCategory::find($id);
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

    public function updateServicesCategory(Request $request){
        DB::beginTransaction();
        $user = auth()->user();
        try {
            $services = ServiceCategory::find( $request->input('id'));
            $services->name = $request->input('name');
            $services->user_id =  $user->id;
            $services->save();

            DB::commit();

            return response()->json(['message' => 'Services Category Updated Successfully!', 'status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services Category', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
    
    public function getServiceCategoryDropdown(Request $request){
        $data = ServiceCategory::select('id','name')->get();
        return $data;
    }
}

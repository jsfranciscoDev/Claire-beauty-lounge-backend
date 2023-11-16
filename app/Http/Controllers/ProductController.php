<?php

namespace App\Http\Controllers;

use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notifications;
use Carbon\Carbon;

class ProductController extends Controller
{
   public function createProduct(Request $request){
        $user = auth()->user();
        DB::beginTransaction();

        try {
            $product = new product();
            $product->name = $request->input('name');
            $product->batch_number = $request->input('batch_number'); 
            $product->price = $request->input('price'); 
            $product->quantity = $request->input('quantity');
            $product->supplier_information = $request->input('supplier_information'); 
            $product->expiration_date = $request->input('expiration_date'); 
            $product->purchase_dates = $request->input('purchase_dates'); 
            $product->user_id = $user->id;
            $product->save();
            DB::commit();

            return response()->json(['message' => 'Product Created Successfully!', 'status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
   }    

   public function getProducts(Request $request){

        $products = product::getQuery() 
        ->join('users','users.id','products.user_id')
        ->leftJoin('user_roles','user_roles.id','users.role_id')
        ->whereNull('products.deleted_at')
        ->select(
            'users.name as username',
            'user_roles.role as role',
            'products.*'
        )
        ->when($request->has('search'), function ($query) use ($request) {
            $searchTerm = $request->input('search');
            // Add a search filter based on the service name
            $query->where('products.name', 'like', '%' . $searchTerm . '%');
        })
        ->paginate(10);
        
        $response = [
            'products' => $products,
            'message' => 'success'
        ];

        return response($response, 201);
   }

    public function removeProduct($id){
        $product = product::find($id);
        if($product){
            $product->delete();
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

    public function updateProduct(Request $request){
            $user = auth()->user();

        DB::beginTransaction();

        try {
            $product = product::find( $request->input('id'));
            $product->name = $request->input('name');
            $product->batch_number = $request->input('batch_number'); 
            $product->purchase_dates = $request->input('purchase_dates'); 
            $product->expiration_date = $request->input('expiration_date'); 
            $product->price = $request->input('price'); 
            $product->quantity = $request->input('quantity'); 
            $product->supplier_information = $request->input('supplier_information'); 
            $product->user_id = $user->id;
            $product->save();

            DB::commit();
            return response()->json(['message' => 'Product Updated Successfully!', 'status' => 'success']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    public function getLowStocks(Request $request){
        $product_details = [];

        $Notifications = Notifications::latest('created_at')->first();

        $lowStockItems = product::where('quantity', '<', $Notifications->quantity)->whereNull('deleted_at')->get();
        if ($lowStockItems->isEmpty()) {
            $response = [
                'products' => $lowStockItems,
                'message' => 'No low stocks'
            ];
        } else {
            $response = [
                'products' => $lowStockItems,
                'message' => 'Low stocks'
            ];
        }
       

        return response($response, 201);
    }

    public function getEpxireStocks(Request $request){
        $product_details = [];

        $now = Carbon::now();
        $expireProducts = Product::whereYear('expiration_date', $now->year)
        ->whereMonth('expiration_date', $now->month)
        ->whereNull('deleted_at')
        ->get();

        if ($expireProducts->isEmpty()) {
            $response = [
                'products' => $expireProducts,
                'message' => 'No Products Will expire soon'
            ];
        } else {
            $response = [
                'products' => $expireProducts,
                'message' => 'Products Will expire soon'
            ];
        }
       
        return response($response, 201);
    }
    
    
}

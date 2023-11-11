<?php

namespace App\Http\Controllers;

use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

   public function getProducts(){

        $products = product::getQuery() 
        ->join('users','users.id','products.user_id')
        ->leftJoin('user_roles','user_roles.id','users.role_id')
        ->whereNull('products.deleted_at')
        ->select(
            'users.name as username',
            'user_roles.role as role',
            'products.*'
        )
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
}

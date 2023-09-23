<?php

namespace App\Http\Controllers;

use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
   public function createProduct(Request $request){

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
            $product->save();
            DB::commit();

            return response()->json(['message' => 'Product Created Successfully!', 'status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
   }    

   public function getProducts(){

        $products = product::getQuery()->whereNull('deleted_at')->paginate(10);
        
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
            $product->save();

            DB::commit();
            return response()->json(['message' => 'Product Updated Successfully!', 'status' => 'success']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error Creating Services', 'status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
}

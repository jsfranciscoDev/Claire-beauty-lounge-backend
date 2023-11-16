<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function store(){
        try{
            DB::beginTransaction();
            Item::create(request()->get('item'));
            DB::commit();
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'message' => 'error'
            ]);
        }
    }

    public function show(){
        return Item::getQuery()
            ->when(request()->get('id'),function($builder,$value){
                return $builder->whereIn('items.id',$value);
            })
            ->join('item_types','item_types.id','items.item_type_id')
            ->select('items.*','item_types.name as type')
            ->get();
    }

    public function update(){
        try{
            DB::beginTransaction();
            Item::where('id',request()->get('id'))->update(request()->get('item'));
            DB::commit();
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'message' => 'error'
            ]);
        }
    }
}

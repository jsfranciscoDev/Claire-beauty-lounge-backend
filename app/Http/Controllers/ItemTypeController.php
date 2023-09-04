<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemTypeController extends Controller
{
    public function store(){
        try{
            DB::beginTransaction();
            ItemType::create(request()->get('item'));
            DB::commit();
        }
        catch(\Exception $e){
            DB::rollback();
            \Log::info($e);
            return response()->json([
                'message' => 'error'
            ]);
        }
    }

    public function show($id){
        return ItemType::getQuery()
            ->where('items.id',$id)
            ->join('item_types','item_types.id','items.item_type_id')
            ->select('items.*','item_types.name as type')
            ->get();
    }

    public function update(){
        try{
            DB::beginTransaction();
            ItemType::where('id',request()->get('id'))->update(request()->get('item'));
            DB::commit();
        }
        catch(\Exception $e){
            DB::rollback();
            \Log::info($e);
            return response()->json([
                'message' => 'error'
            ]);
        }
    }
}

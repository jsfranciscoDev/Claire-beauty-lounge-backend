<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(){
        try{
            DB::beginTransaction();
            Transaction::create(request()->get('transaction'));
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

    public function update(){
        try{
            DB::beginTransaction();
            Transaction::where('id',request()->get('id'))->update(request()->get('item'));
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

    public function show(){
        return Transaction::getQuery()
            ->join('users as customers','customers.id','transactions.customer_user_id')
            ->join('users as employee','customers.id','transactions.employee_user_id')
            ->whereNull('transactions.deleted_at')
            ->where('employee.active',1)
            ->when(request()->get('status'),function($builder,$value){
                return $builder->whereIn('transactions.status',$value);
            })
            ->when(request()->get('has_date'),function($builder,$value){
                return $builder->whereBetweem(DB::raw('date_format(transactions.craeted_at,"%Y-%m-%d")',[request()->get('date_from'),request()->get('date_to')]));
            })
            ->select('customers.name as customer','employee.name as employee','transactions.id',DB::raw('date_format(created_at,"%Y-%m-%d") as created'))
            ->get();
    }
}

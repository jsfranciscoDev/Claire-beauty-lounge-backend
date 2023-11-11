<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class product extends Model
{
    use HasFactory , SoftDeletes;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'name', 
        'user_id', 
        'batch_numbers',
        'purchase_dates',
        'expiration_dates',
        'supplier_inforation',
        'quantity',
        'price',
    ];
}

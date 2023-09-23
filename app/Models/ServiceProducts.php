<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceProducts extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'services_products';
    
    protected $fillable = [
        'product_id', 
        'services_id',
        'quantity'
    ];

}

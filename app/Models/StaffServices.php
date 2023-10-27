<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffServices extends Model
{
    use HasFactory;

    protected $table = 'staff_services';
    
    protected $fillable = [
        'user_id',
        'service_category_id',
    ];
}

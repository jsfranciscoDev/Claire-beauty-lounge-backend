<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointment';

    protected $fillable = [
        'transaction_id',
        'service_type',
        'user_id',
        'status',
        'date',
        'user_staff'
    ];

    // public function service()
    // {
    //     return $this->hasOne(Services::class, 'service_type', 'id');
    // }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    protected $table = 'notification';

    protected $fillable = [
        'quantity',
        'phone_number',
        'email'
    ];
}

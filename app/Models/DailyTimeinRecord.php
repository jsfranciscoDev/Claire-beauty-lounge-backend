<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTimeinRecord extends Model
{
    use HasFactory;
    
    protected $table = 'daily_time_in_record';

    protected $fillable = [
        'user_id',
        'time_in',
        'time_out',
        'date',
    ];
}

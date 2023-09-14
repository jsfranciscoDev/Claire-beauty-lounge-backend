<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profile';

    protected $fillable = [
        'user_id',
        'profile_picture',
        'path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

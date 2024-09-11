<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    use HasFactory;
    protected $fillable = [
        'gender',
        'salary',
        'phone',
        'birthday',
        'l_name',
        'f_name',
        'address_id',
        'user_id',
    ];
}

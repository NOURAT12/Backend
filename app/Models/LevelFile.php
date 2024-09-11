<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelFile extends Model
{
    use HasFactory;
    protected $fillable = [
        'file_id',
        'level_id'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id',
        'user_id',
        'value'
    ];
    public function course()
    {
        return $this->belongsTo(Course::class)->select(['name','id','level_id']);
    }
}

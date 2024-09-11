<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id',
        'user_id',
        'date',
        'type',
        'late_time',
        'cause',
    ];
    public $appends=[
        'student_name',
    ];
    public function getStudentNameAttribute()
    {
        return Student::where('user_id',$this->user_id)->first()->full_name;
    }
}

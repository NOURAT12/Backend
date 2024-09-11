<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;
    protected $fillable = [
        'exam_id',
        'user_id',
        'mark'
    ];

    public $appends=[
        'student_name',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStudentNameAttribute()
    {
        return Student::where('user_id',$this->user_id)->select('full_name')->first();
    }
}

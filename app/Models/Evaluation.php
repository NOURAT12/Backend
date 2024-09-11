<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lecture_id',
        'created_by',
        'value',
        'course_id',
        'subject_id'
    ];

    public $appends=[
        'student_name',
    ];
    public function getStudentNameAttribute()
    {
        return Student::where('user_id',$this->user_id)->select('full_name')->first();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->select('username','id');
    }
    public function lecture()
    {
        return $this->belongsTo(Lecture::class)->select('name','id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function scopeStudent($query)
    {
        return $query->with('lecture')->with('subject');
    }
}

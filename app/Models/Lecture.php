<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'subject_id',
        'course_id'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class)->select('name','id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class)->select('name','id','level_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizLevel extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id',
        'quiz_id'
    ];
}

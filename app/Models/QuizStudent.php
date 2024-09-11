<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizStudent extends Model
{
    use HasFactory;
    protected $fillable = [
        'quiz_id',
        'user_id',
        'state',
        'question_true_number',
        'mark'
    ];
    public function quiz()
    {
        return $this->belongsTo(User::class);
    }
}

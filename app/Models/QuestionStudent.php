<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionStudent extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_id',
        'user_id',
        'solve'
    ];
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

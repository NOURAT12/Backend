<?php

namespace App\Models;

use App\Traits\QuizTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory,QuizTrait;
    protected $fillable = [
        'name',
        'level_id',
        'subject_id',
        'start',
        'total_time',
        'question_number',
        'created_by',
        'type'
    ];
    public $appends=[
        'status'
    ];
    public function getStatusAttribute()
    {
      if($this->start){
       return  $this->getResultDate($this);
      }
      return ['available'=>true,'message'=>'متاح'];
    }

    public function questions(): HasMany
    {
      return $this->hasMany(Question::class);
    }
    public function quizStudent(): HasMany
    {
      return $this->hasMany(QuizStudent::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class)->select('name','id');
    }
    public function level()
    {
        return $this->belongsTo(Level::class)->select('name','id');
    }
    public function scopeStudent($query)
    {
        return $query->with('level','subject');
    }
}

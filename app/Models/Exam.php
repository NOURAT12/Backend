<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'name',
        'course_id',
        'subject_id',
        'date',
        'type',
        'min_mark',
        'mark',
        'solve',
        'questions',
    ];
    public $appends=[
        'solve_url',
        'questions_url',
    ];


    public function course()
    {
        return $this->belongsTo(Course::class)->select('name','id','level_id');
    }
    public function getQuestionsUrlAttribute()
    {
        return $this->getImage($this->questions);
    }
    public function getSolveUrlAttribute()
    {
        return $this->getImage($this->solve);
    }

    public function examFiles()
    {
        return $this->hasMany(ExamFile::class);
    }

    public function scopeStudent($query)
    {
        return $query->with('subject');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class)->select('name','id');
    }
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }
}

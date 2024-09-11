<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'student_num',
        'course_id',
        'level_id'
    ];
    public $appends=[
        'has_timeTable',
         'course',
        'level'
    ];
    public function getHasTimeTableAttribute()
    {
     $s= TimeTable::where('section_id',$this->id)->exists();
     if($s){
        return true;
     }else
      return false;
    }
    public function getCourseAttribute()
    {
     $s= Course::find($this->course_id);

      return $s->name;
    }
    public function getLevelAttribute()
    {
        $s= Level::find($this->level_id);

        return $s->name;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function sectionTeacher()
    {
        return $this->hasMany(SectionTeacher::class);
    }
    public function timeTables()
    {
        return $this->hasMany(TimeTable::class);
    }
}

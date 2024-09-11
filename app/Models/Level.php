<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'name',
        'logo'
    ];
    public $appends=[
        'logo_url',
        'course_number',
        'subject_number',
    ];
    public function getLogoUrlAttribute()
    {
        return $this->getImage($this->logo);
    }
    public function getCourseNumberAttribute()
    {
       $courses= Course::where('level_id',$this->id)->count();

        return $courses;
    }
    public function getSubjectNumberAttribute()
    {
       $subjects= Subject::where('level_id',$this->id)->get();

        return count($subjects);
    }
    public function subjects()
    {
        return $this->hasMany(Subject::class)->select('name','id','logo','level_id');
    }
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}

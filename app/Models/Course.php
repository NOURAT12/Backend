<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'fees',
        'start_date',
        'study_plan',
        'level_id',
        'name',
        'logo',
        'active',
        'end_date',
    ];
    public static $teacherScope = false;
    protected static $userId;
    public $appends=[
        'logo_url','sections','level_name'
    ];
    protected function getArrayableAppends()
    {
        if (self::$teacherScope){
            return ['logo_url','sections'];
        }

        return ['logo_url','level_name'];
    }
    public function getSectionsAttribute()
    {
        if (self::$userId) {
            return SectionTeacher::select('section_id','subject_id','user_id')->where('user_id', self::$userId)
                ->where('course_id', $this->id)
                ->get();
        }

        return null;
    }

    public function getLogoUrlAttribute()
    {
        return $this->getImage($this->logo);
    }
    public function getLevelNameAttribute()
    {
        return Level::find($this->level_id)->name;
    }

    public function level()
    {
        return $this->belongsTo(Level::class)->select('name','id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function scopeStudent($query)
    {
        return $query->with('level');
    }
    public function scopeTeacher($query,$user_id)
    {
        self::$teacherScope = true;
        self::$userId = $user_id;
        return $query;
    }
}

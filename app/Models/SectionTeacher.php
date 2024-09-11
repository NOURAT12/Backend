<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionTeacher extends Model
{
    use HasFactory;
    protected $fillable = [
        'section_id',
        'user_id',
        'course_id',
        'subject_id',
    ];

    public $appends = [
        'subject',
        'teacher'
    ];
    public function getSubjectAttribute()
    {
        return Subject::select(['name', 'id', 'logo'])->find($this->subject_id);
    }
    public function getTeacherAttribute()
    {
        return Teacher::where('user_id', $this->user_id)->first()->full_name;
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}

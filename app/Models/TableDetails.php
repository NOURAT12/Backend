<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TableDetails extends Model
{
    use HasFactory;
    protected $fillable = [
        'time_table_id',
        'subject_id',
        'user_id',
        'time',
        'duration'
    ];
    public $appends=[
        'subject','teacher'
    ];
    public function getSubjectAttribute()
    {
        return Subject::select('name','id','logo')->find($this->subject_id);
    }
    public function getTeacherAttribute()
    {
        $teacher= Teacher::where('user_id',$this->user_id)->first();
        return $teacher->f_name.' '.$teacher->l_name;
    }

    public function subject(): HasOne
    {
      return $this->hasOne(Subject::class);
    }
}

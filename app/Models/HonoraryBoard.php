<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HonoraryBoard extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'description',
        'study_year',
    ];
    public function student()
    {
        return $this->belongsTo(Student::class)->select('id','logo','full_name','birthday','gender');
    }

}

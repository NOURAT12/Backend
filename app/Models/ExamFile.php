<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamFile extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'exam_id',
        'type',
        'file',

    ];
    public $appends=[
        'file_url',
    ];
    public function getFileUrlAttribute()
    {
        return $this->getImage($this->file);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}

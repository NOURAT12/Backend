<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'quiz_id',
        'solve',
        'E',
        'D',
        'C',
        'B',
        'A',
        'image',
        'title'
    ];
    public $appends=[
        'image_url',
    ];
    public function getImageUrlAttribute()
    {
        return $this->getImage($this->image);
    }

    public function questionStudents()
    {
        return $this->hasMany(questionStudent::class)->select('solve','question_id');
    }
}

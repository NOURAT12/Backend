<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievements extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'description',
        'file',
    ];
    public $appends=[
        'file_url'
    ];
    public function getFileUrlAttribute()
    {
        return $this->getImage($this->file);
    }
}

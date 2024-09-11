<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'exp_date',
        'active',
        'file',
        'description',
        'title',
    ];
    public $appends=[
        'file_url',
    ];
    public function getFileUrlAttribute()
    {
        return $this->getImage($this->file);
    }
}

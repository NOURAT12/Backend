<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'name',
        'level_id',
        'logo'
    ];
    public $appends=[
        'logo_url',
    ];
    public function getLogoUrlAttribute()
    {
        return $this->getImage($this->logo);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}

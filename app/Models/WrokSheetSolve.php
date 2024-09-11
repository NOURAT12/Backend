<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrokSheetSolve extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'file_id',
        'solve',
        'type'
    ];
    public $appends=[
        'solve_url',
    ];
    public function getSolveUrlAttribute()
    {
        return $this->getImage($this->solve);
    }
}

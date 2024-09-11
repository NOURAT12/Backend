<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'name',
        'solve',
        'type',
        'size',
        'level_id',
        'subject_id',
        'created_by',
        'path'
    ];
    public $appends=[
        'solve_url','file_url'
    ];
    public function getSolveUrlAttribute()
    {
        return $this->getImage($this->solve);
    }
    public function getFileUrlAttribute()
    {
        return $this->getImage($this->path);
    }
    public function worksheetsolves(): HasMany
    {
      return $this->hasMany(WrokSheetSolve::class);
    }
    public function scopeStudent($query)
    {
        return $query->with('level','subject');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class)->select('name','id');
    }
    public function level()
    {
        return $this->belongsTo(Level::class)->select('name','id');
    }
}

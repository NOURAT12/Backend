<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeTable extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id',
        'section_id',
        'day'
    ];

    public function table_details(): HasMany
    {
      return $this->hasMany(TableDetails::class);
    }
    
    public function section()
    {
      return $this->belongsTo(Section::class);
    }
}

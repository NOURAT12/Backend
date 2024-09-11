<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'gender',
        'parent_phone',
        'phone',
        'mother_name',
        'birthday',
        'father_name',
        'full_name',
        'l_name',
        'f_name',
        'address_id',
        'user_id',
        'logo',
    ];
    public static $withlogo = true;

    public $appends=[
        'logo_url',
    ];
    protected function getArrayableAppends()
    {
        if (self::$withlogo){
            return ['logo_url'];
        }

        return [];
    }
    public function getLogoUrlAttribute()
    {
        return $this->getImage($this->logo);
    }
    public function address()
    {
        return $this->belongsTo(Address::class)->select([
            'id',
            'city',
            'town',
            'section',
            'description',
        ]);
    }
    public function scopeUser($query)
    {
        return $query->select([
            'user_id as id',
            'gender',
            'parent_phone',
            'phone',
            'mother_name',
            'birthday',
            'father_name',
            'full_name',
            'l_name',
            'f_name',
            'address_id',
            'created_at', 'updated_at'
        ]);
    }

    public function scopeSearch($query)
    {
         self::$withlogo=false;
        return $query->select([
            'full_name',
        ]);
    }
}

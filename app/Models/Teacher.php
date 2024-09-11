<?php

namespace App\Models;

use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory,ImageTrait;
    protected $fillable = [
        'gender',
        'phone',
        'birthday',
        'f_name',
        'l_name',
        'address_id',
        'full_name',
        'description',
        'user_id',
        'logo'
    ];
    public $appends=[
        'logo_url',
    ];
    public function getLogoUrlAttribute()
    {
        return $this->getImage($this->logo);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->setFullNameAttribute();
        });

        static::updating(function ($model) {
            $model->setFullNameAttribute();
        });
    }

    protected function setFullNameAttribute()
    {
        $this->attributes['full_name'] = trim($this->f_name . ' ' . $this->l_name);
    }
    public function scopeUser($query)
    {
        return $query->select([
            'user_id as id',
            'gender',
            'phone',
            'birthday',
            'full_name',
            'l_name',
            'f_name',
            'description',
            'address_id',
            'created_at', 'updated_at'
        ]);
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
}

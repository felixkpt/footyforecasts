<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaracraftTech\LaravelDynamicModel\DynamicModel;
use LaracraftTech\LaravelDynamicModel\DynamicModelFactory;

class Competition extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'country_id',
        'user_id',
        'logo',
        'status',
    ];

    function teams()
    {

      return $this->hasMany(Team::class);
    }


    protected $hidden = ['id', 'country_id', 'user_id'];
    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Competition extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'abbreviation',
        'country_id',
        'url',
        'img',
        'last_fetch',
        'last_detailed_fetch',
        'user_id',
        'status',
        'is_domestic',
    ];

    function country()
    {
      return $this->belongsTo(Country::class);
    }

    function teams()
    {
      return $this->hasMany(Team::class);
    }


    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }
}

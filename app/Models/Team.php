<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'url',
        'competition_id',
        'country_id',
        'img',
        'user_id',
        'status',
    ];

    protected $hidden = ['id', 'country_id', 'user_id'];
    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }
}

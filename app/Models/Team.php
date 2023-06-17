<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'competition_id',
        'country_id',
        'img',
        'last_fetch',
        'user_id',
        'status',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }
}

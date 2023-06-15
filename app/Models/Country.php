<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'user_id',
        'status',
    ];

    function competitions()
    {
        return $this->hasMany(Competition::class);
    }

    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }
}

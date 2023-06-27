<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use LaracraftTech\LaravelDynamicModel\DynamicModel;

// class Game extends Model implements DynamicModelInterface (this would also work)
class Game extends DynamicModel
{
    use HasUlids;
    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }
}

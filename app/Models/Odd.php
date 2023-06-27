<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odd extends Model
{
    use HasFactory, HasUlids;
    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }

    protected $fillable = [
        'date_time',
        'date',
        'time',
        'home_team',
        'away_team',
        'competition_id',
        'source',
        'home_win_odds',
        'draw_odds',
        'away_win_odds',
        'over_odds',
        'under_odds',
        'gg_odds',
        'ng_odds',
        'game_id',

    ];
}

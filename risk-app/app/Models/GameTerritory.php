<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameTerritory extends Model
{
    use HasFactory;

    protected $table = 'game_territories';

    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'territory_id',
        'player_id',
        'armies',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['territory'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['territory_id', 'player_id'];

    public function getTerritoryIdAttribute()
    {
        return $this->attributes['territory_id'];
    }

    public function getPlayerIdAttribute()
    {
        return $this->attributes['player_id'];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}

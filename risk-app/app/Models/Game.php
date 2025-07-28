<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'current_turn_player_id',
        'turn_phase',
        'winner_player_id',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function gameTerritories(): HasMany
    {
        return $this->hasMany(GameTerritory::class);
    }

    public function currentTurnPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_turn_player_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_player_id');
    }
}

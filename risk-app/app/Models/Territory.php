<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Territory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'continent_id',
        'geo_data',
    ];

    protected $casts = [
        'geo_data' => 'array',
    ];

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function adjacencies(): BelongsToMany
    {
        return $this->belongsToMany(Territory::class, 'territory_adjacency', 'territory_id', 'adjacent_territory_id');
    }
}

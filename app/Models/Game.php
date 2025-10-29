<?php

namespace App\Models;

use App\GameStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'game_code',
        'status',
        'game_type',
        'current_round',
        'max_rounds',
        'current_turn_player_id',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function currentTurnPlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'current_turn_player_id');
    }
}

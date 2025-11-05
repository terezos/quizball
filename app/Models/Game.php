<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'game_code',
        'status',
        'game_type',
        'is_matchmaking',
        'matchmaking_started_at',
        'current_round',
        'max_rounds',
        'game_pace',
        'current_turn_player_id',
        'started_at',
        'completed_at',
        'is_forfeited',
        'forfeited_by_player_id',
        'sport',
        'ai_difficulty',
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
            'is_matchmaking' => 'boolean',
            'matchmaking_started_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function gamePlayers(): HasMany
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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'game_category');
    }
}

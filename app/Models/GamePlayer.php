<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GamePlayer extends Model
{
    protected $fillable = [
	    'game_id',
	    'user_id',
	    'guest_name',
	    'session_id',
	    'is_ai',
	    'score',
	    'player_order',
    ];

    protected function casts(): array
    {
        return [
            'is_ai' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->is_ai) {
            return 'AI Opponent';
        }

        return $this->user?->name ?? $this->guest_name ?? 'Guest';
    }
}

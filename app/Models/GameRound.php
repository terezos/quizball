<?php

namespace App\Models;

use App\Enums\DifficultyLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRound extends Model
{
    protected $fillable = [
        'game_id',
        'game_player_id',
        'question_id',
        'round_number',
        'category_id',
        'difficulty',
        'player_answer',
        'is_correct',
        'used_2x_powerup',
        'used_5050_powerup',
        'points_earned',
        'time_taken',
        'started_at',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => DifficultyLevel::class,
            'is_correct' => 'boolean',
            'used_2x_powerup' => 'boolean',
            'started_at' => 'datetime',
            'answered_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function gamePlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

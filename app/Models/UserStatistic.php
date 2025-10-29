<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatistic extends Model
{
    protected $fillable = [
        'user_id',
        'games_played',
        'games_won',
        'games_lost',
        'total_score',
        'total_questions_answered',
        'correct_answers',
        'category_stats',
    ];

    protected function casts(): array
    {
        return [
            'category_stats' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getWinRateAttribute(): float
    {
        if ($this->games_played === 0) {
            return 0;
        }

        return round(($this->games_won / $this->games_played) * 100, 2);
    }

    public function getAccuracyAttribute(): float
    {
        if ($this->total_questions_answered === 0) {
            return 0;
        }

        return round(($this->correct_answers / $this->total_questions_answered) * 100, 2);
    }
}

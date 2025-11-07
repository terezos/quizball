<?php

namespace App\Models;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'category_id',
        'created_by',
        'question_text',
        'image_url',
        'question_type',
        'difficulty',
        'is_active',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'source_url',
    ];

    protected function casts(): array
    {
        return [
            'question_type' => QuestionType::class,
            'difficulty' => DifficultyLevel::class,
            'status' => QuestionStatus::class,
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function gameRounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function isUsedInActiveGames(): bool
    {
        // Check if question is used in any round of an active game
        $usedInRounds = $this->gameRounds()
            ->whereHas('game', function ($query) {
                $query->where('status', \App\Enums\GameStatus::Active);
            })
            ->exists();

        if ($usedInRounds) {
            return true;
        }

        // Check if question is pre-cached in any active game
        $activeGames = Game::where('status', \App\Enums\GameStatus::Active)->pluck('id');

        foreach ($activeGames as $gameId) {
            // Check all possible category/difficulty combinations
            $categories = \Cache::get("game:{$gameId}:categories");

            if ($categories) {
                foreach ($categories as $category) {
                    foreach (['easy', 'medium', 'hard'] as $difficulty) {
                        $cachedQuestionId = \Cache::get("game:{$gameId}:question:{$category['id']}:{$difficulty}");

                        if ($cachedQuestionId === $this->id) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function canBeEdited(): bool
    {
        return ! $this->isUsedInActiveGames();
    }

    public function canBeDeleted(): bool
    {
        return ! $this->isUsedInActiveGames();
    }
}

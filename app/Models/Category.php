<?php

namespace App\Models;

use App\Enums\SportEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'order',
        'is_active',
        'sport',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sport' => SportEnum::class,
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function gameRounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function isUsedInActiveGames(): bool
    {
        // Check if category is in any active game (from game_category pivot table)
        $usedInDatabase = Game::where('status', \App\Enums\GameStatus::Active)
            ->whereHas('categories', function ($query) {
                $query->where('categories.id', $this->id);
            })
            ->exists();

        if ($usedInDatabase) {
            return true;
        }

        // Check if category is cached in any active game
        $activeGames = Game::where('status', \App\Enums\GameStatus::Active)->pluck('id');

        foreach ($activeGames as $gameId) {
            $cachedCategories = \Cache::get("game:{$gameId}:categories");

            if ($cachedCategories) {
                foreach ($cachedCategories as $category) {
                    if ($category['id'] === $this->id) {
                        return true;
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

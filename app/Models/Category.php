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
}

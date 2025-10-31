<?php

namespace App\Enums;

enum DifficultyLevel: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';

    public function points(): int
    {
        return match($this) {
            self::Easy => 1,
            self::Medium => 2,
            self::Hard => 3,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Easy => 'Easy (1pt)',
            self::Medium => 'Medium (2pts)',
            self::Hard => 'Hard (3pts)',
        };
    }
}

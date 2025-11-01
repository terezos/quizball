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
            self::Easy => 'Εύκολo (1π)',
            self::Medium => 'Μέτριo (2π)',
            self::Hard => 'Δύσκολo (3π)',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Easy => 'bg-green-100 text-green-800',
            self::Medium => 'bg-yellow-100 text-yellow-800',
            self::Hard => 'bg-red-100 text-red-800',
        };
    }
}

<?php

namespace App\Enums;

enum SportEnum: string
{
    case Football = 'football';
    case Basketball = 'basketball';

    public function label(): string
    {
        return match($this) {
            self::Football => 'Ποδόσφαιρο',
            self::Basketball => 'Μπάσκετ',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Football => '⚽',
            self::Basketball => '🏀',
        };
    }
}

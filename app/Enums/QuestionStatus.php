<?php

namespace App\Enums;

enum QuestionStatus: string
{
    case Approved = 'approved';
    case Pending = 'pending';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Approved => 'Εγκεκριμένο',
            self::Pending => 'Σε εκκρεμότητα',
            self::Rejected => 'Απορριφθέν',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Approved => 'bg-green-100 text-green-800',
            self::Pending => 'bg-yellow-100 text-yellow-800',
            self::Rejected => 'bg-red-100 text-red-800',
        };
    }

}

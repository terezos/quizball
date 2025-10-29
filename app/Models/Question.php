<?php

namespace App\Models;

use App\DifficultyLevel;
use App\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'category_id',
        'created_by',
        'question_text',
        'question_type',
        'difficulty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'question_type' => QuestionType::class,
            'difficulty' => DifficultyLevel::class,
            'is_active' => 'boolean',
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

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function gameRounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }
}

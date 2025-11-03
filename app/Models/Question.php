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
}

<?php

namespace App\Services;

use App\Enums\DifficultyLevel;
use App\Models\Category;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Question;
use Exception;

class AIOpponentService
{
    /**
     * @throws Exception
     */
    public function playTurn(Game $game, GamePlayer $aiPlayer): void
    {
        try {
            $gameService = app(GameService::class);

            sleep(rand(2, 5));

            $category = $this->selectCategory($game);
            $gameService->selectCategory($game, $aiPlayer, $category);

            sleep(rand(1, 2));

            $difficulty = $this->selectDifficulty($game, $aiPlayer, $category);
            $question = $gameService->selectDifficulty($game, $aiPlayer, $difficulty);

            if (!$question) {
                return;
            }

            $answerDelay = rand(5, 45);
            sleep(min($answerDelay, 5));

            $answer = $this->generateAnswer($question, $difficulty, $game);
            $gameService->submitAnswer($game, $aiPlayer, $answer);
        }catch (Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }

    protected function selectCategory(Game $game): Category
    {
        $usedCombinations = app(GameService::class)->getUsedCategoryDifficulties($game);

        $categoryUsage = collect($usedCombinations)->groupBy('category_id')
            ->map(fn($rounds) => $rounds->count());

        $gameCategories = $game->categories()->get();

        $availableCategories = $gameCategories->filter(function($category) use ($categoryUsage) {
            $usedCount = $categoryUsage->get($category->id, 0);
            return $usedCount < 3;
        });

        if ($availableCategories->isEmpty()) {
            /** @var Category $category */
            $category = $gameCategories->random();
            return $category;
        }

        /** @var Category $category */
        $category = $availableCategories->random();
        return $category;
    }

    protected function selectDifficulty(Game $game, GamePlayer $aiPlayer, Category $category): DifficultyLevel
    {
        $players = $game->players;
        $opponent = $players->where('id', '!=', $aiPlayer->id)->first();

        // Get used difficulties for this category
        $usedDifficulties = \App\Models\GameRound::where('game_id', $game->id)
            ->where('category_id', $category->id)
            ->pluck('difficulty')
            ->map(fn($d) => $d->value)
            ->toArray();

        // Get available difficulties
        $allDifficulties = [
            DifficultyLevel::Easy,
            DifficultyLevel::Medium,
            DifficultyLevel::Hard,
        ];

        $availableDifficulties = collect($allDifficulties)
            ->filter(fn($diff) => !in_array($diff->value, $usedDifficulties));

        if ($availableDifficulties->isEmpty()) {
            // Fallback
            return DifficultyLevel::Medium;
        }

        // AI strategy with available difficulties
        $rand = rand(1, 100);

        // Try to select based on AI strategy
        if ($rand <= 20 && $availableDifficulties->contains(DifficultyLevel::Easy)) {
            return DifficultyLevel::Easy;
        }
        if ($rand <= 70 && $availableDifficulties->contains(DifficultyLevel::Medium)) {
            return DifficultyLevel::Medium;
        }
        if ($availableDifficulties->contains(DifficultyLevel::Hard)) {
            return DifficultyLevel::Hard;
        }

        // Return random available difficulty
        return $availableDifficulties->random();
    }

    protected function generateAnswer(Question $question, DifficultyLevel $difficulty, Game $game): string|array
    {
        $aiLevel = $game->ai_difficulty ?? 2;

        $accuracyRate = match ($aiLevel) {
            1 => match ($difficulty) {
                DifficultyLevel::Easy => 60,
                DifficultyLevel::Medium => 40,
                DifficultyLevel::Hard => 20,
            },
            2 => match ($difficulty) {
                DifficultyLevel::Easy => 85,
                DifficultyLevel::Medium => 70,
                DifficultyLevel::Hard => 50,
            },
            3 => match ($difficulty) {
                DifficultyLevel::Easy => 95,
                DifficultyLevel::Medium => 90,
                DifficultyLevel::Hard => 80,
            },
            default => 70,
        };

        $isCorrect = rand(1, 100) <= $accuracyRate;

        if (!$isCorrect) {
            return $this->generateWrongAnswer($question);
        }

        return $this->generateCorrectAnswer($question);
    }

    protected function generateCorrectAnswer(Question $question): string|array
    {
        $correctAnswers = $question->answers()->where('is_correct', true)->get();

        if ($question->question_type->value === 'top_5') {
            return $correctAnswers->take(5)->pluck('answer_text')->toArray();
        }

        $answer = $correctAnswers->first();

        if ($question->question_type->value === 'multiple_choice') {
            return (string) $answer->id;
        }

        return $answer->answer_text;
    }

    protected function generateWrongAnswer(Question $question): string|array
    {
        if ($question->question_type->value === 'top_5') {
            $correctAnswers = $question->answers()->where('is_correct', true)->take(5)->get();
            $wrongCount = rand(1, 3);
            $correctCount = 5 - $wrongCount;

            $result = $correctAnswers->take($correctCount)->pluck('answer_text')->toArray();

            for ($i = 0; $i < $wrongCount; $i++) {
                $result[] = 'Wrong Answer ' . ($i + 1);
            }

            return $result;
        }

        if ($question->question_type->value === 'multiple_choice') {
            $wrongAnswers = $question->answers()->where('is_correct', false)->get();

            if ($wrongAnswers->isNotEmpty()) {
                return (string) $wrongAnswers->random()->id;
            }
        }

        return 'Wrong Answer';
    }
}

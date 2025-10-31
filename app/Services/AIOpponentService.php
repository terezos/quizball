<?php

namespace App\Services;

use App\Enums\DifficultyLevel;
use App\Models\Category;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Question;

class AIOpponentService
{
    public function playTurn(Game $game, GamePlayer $aiPlayer): void
    {
        $gameService = app(GameService::class);

        sleep(rand(2, 5));

        $category = $this->selectCategory($game);
        $gameService->selectCategory($game, $aiPlayer, $category);

        sleep(rand(1, 2));

        $difficulty = $this->selectDifficulty($game, $aiPlayer);
        $question = $gameService->selectDifficulty($game, $aiPlayer, $difficulty);

        if (!$question) {
            return;
        }

        $answerDelay = rand(5, 45);
        sleep(min($answerDelay, 10));

        $answer = $this->generateAnswer($question, $difficulty);
        $gameService->submitAnswer($game, $aiPlayer, $answer);
    }

    protected function selectCategory(Game $game): Category
    {
        return Category::where('is_active', true)
            ->inRandomOrder()
            ->first();
    }

    protected function selectDifficulty(Game $game, GamePlayer $aiPlayer): DifficultyLevel
    {
        $players = $game->players;
        $opponent = $players->where('id', '!=', $aiPlayer->id)->first();

        if (!$opponent) {
            return DifficultyLevel::Medium;
        }

        $scoreDifference = $aiPlayer->score - $opponent->score;

        if ($scoreDifference < -3) {
            $rand = rand(1, 100);
            if ($rand <= 20) {
                return DifficultyLevel::Easy;
            }
            if ($rand <= 70) {
                return DifficultyLevel::Medium;
            }

            return DifficultyLevel::Hard;
        }

        if ($scoreDifference > 3) {
            $rand = rand(1, 100);
            if ($rand <= 10) {
                return DifficultyLevel::Easy;
            }
            if ($rand <= 40) {
                return DifficultyLevel::Medium;
            }

            return DifficultyLevel::Hard;
        }

        $rand = rand(1, 100);
        if ($rand <= 30) {
            return DifficultyLevel::Easy;
        }
        if ($rand <= 80) {
            return DifficultyLevel::Medium;
        }

        return DifficultyLevel::Hard;
    }

    protected function generateAnswer(Question $question, DifficultyLevel $difficulty): string|array
    {
        $accuracyRate = match ($difficulty) {
            DifficultyLevel::Easy => 90,
            DifficultyLevel::Medium => 70,
            DifficultyLevel::Hard => 50,
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

<?php

namespace App\Services;

use App\DifficultyLevel;
use App\Models\Category;
use App\Models\Question;
use App\QuestionType;

class QuestionService
{
    public function getRandomQuestion(Category $category, DifficultyLevel $difficulty): ?Question
    {
        return Question::query()
            ->where('category_id', $category->id)
            ->where('difficulty', $difficulty)
            ->where('is_active', true)
            ->with('answers')
            ->inRandomOrder()
            ->first();
    }

    public function validateAnswer(Question $question, string|array $playerAnswer): bool
    {
        return match ($question->question_type) {
            QuestionType::TextInput => $this->validateTextInput($question, $playerAnswer),
            QuestionType::MultipleChoice => $this->validateMultipleChoice($question, $playerAnswer),
            QuestionType::TopFive => $this->validateTopFive($question, $playerAnswer),
        };
    }

    protected function validateTextInput(Question $question, string $playerAnswer): bool
    {
        $correctAnswer = $question->answers()
            ->where('is_correct', true)
            ->first();

        if (!$correctAnswer) {
            return false;
        }

        return $this->normalizeText($playerAnswer) === $this->normalizeText($correctAnswer->answer_text);
    }

    protected function validateMultipleChoice(Question $question, string $playerAnswer): bool
    {
        $correctAnswer = $question->answers()
            ->where('is_correct', true)
            ->first();

        if (!$correctAnswer) {
            return false;
        }

        return $correctAnswer->id == $playerAnswer ||
               $this->normalizeText($correctAnswer->answer_text) === $this->normalizeText($playerAnswer);
    }

    protected function validateTopFive(Question $question, array $playerAnswers): bool
    {
        if (count($playerAnswers) < 5) {
            return false;
        }

        $correctAnswers = $question->answers()
            ->where('is_correct', true)
            ->pluck('answer_text')
            ->map(fn($text) => $this->normalizeText($text))
            ->toArray();

        if (count($correctAnswers) < 5) {
            return false;
        }

        $normalizedPlayerAnswers = array_map(
            fn($answer) => $this->normalizeText($answer),
            array_slice($playerAnswers, 0, 5)
        );

        $matchCount = 0;
        foreach ($normalizedPlayerAnswers as $playerAnswer) {
            if (in_array($playerAnswer, $correctAnswers)) {
                $matchCount++;
            }
        }

        return $matchCount >= 5;
    }

    protected function normalizeText(string $text): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $text)));
    }
}

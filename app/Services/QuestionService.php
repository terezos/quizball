<?php

namespace App\Services;

use App\DifficultyLevel;
use App\Models\Category;
use App\Models\Question;
use App\QuestionType;

class QuestionService
{
    public function __construct(
        protected AIAnswerValidationService $aiValidation
    ) {}

    public function getRandomQuestion(Category $category, DifficultyLevel $difficulty, ?int $excludeCreatorId = null): ?Question
    {
        return Question::query()
            ->where('category_id', $category->id)
            ->where('difficulty', $difficulty)
            ->where('is_active', true)
            ->where('status', 'approved')  // Only approved questions
            ->when($excludeCreatorId, function ($query, $creatorId) {
                $query->where('created_by', '!=', $creatorId);
            })
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
        $correctAnswers = $question->answers()
            ->where('is_correct', true)
            ->pluck('answer_text')
            ->toArray();

        if (empty($correctAnswers)) {
            return false;
        }

        // First try exact match (normalized)
        $normalizedPlayerAnswer = $this->normalizeText($playerAnswer);
        foreach ($correctAnswers as $correct) {
            if ($normalizedPlayerAnswer === $this->normalizeText($correct)) {
                return true;
            }
        }
        // If no exact match, try AI validation
        if (config('quiz.ai_validation_enabled', true) && config('openai.api_key')) {
            try {
                $result = $this->aiValidation->validateAnswer(
                    $question->question_text,
                    $correctAnswers,
                    $playerAnswer
                );

                return $result['is_correct'];
            } catch (\Exception $e) {
                \Log::warning('AI validation failed, falling back to exact match', [
                    'question_id' => $question->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return false;
    }

    protected function validateMultipleChoice(Question $question, string $playerAnswer): bool
    {
        $correctAnswer = $question->answers()
            ->where('is_correct', true)
            ->first();

        if (! $correctAnswer) {
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
            ->toArray();

        if (count($correctAnswers) < 5) {
            return false;
        }

        $matchCount = 0;

        // Check each player answer
        foreach (array_slice($playerAnswers, 0, 5) as $playerAnswer) {
            $isMatch = false;

            // First try exact match
            $normalizedPlayerAnswer = $this->normalizeText($playerAnswer);
            foreach ($correctAnswers as $correct) {
                if ($normalizedPlayerAnswer === $this->normalizeText($correct)) {
                    $isMatch = true;
                    break;
                }
            }

            // If no exact match, try AI validation
            if (! $isMatch && config('quiz.ai_validation_enabled', true) && config('openai.api_key')) {
                try {
                    $result = $this->aiValidation->validateAnswer(
                        $question->question_text,
                        $correctAnswers,
                        $playerAnswer
                    );

                    if ($result['is_correct']) {
                        $isMatch = true;
                    }
                } catch (\Exception $e) {
                    // Continue with next answer
                }
            }

            if ($isMatch) {
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

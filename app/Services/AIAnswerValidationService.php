<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class AIAnswerValidationService
{
    /**
     * Validate if a user's answer is semantically correct
     *
     * Handles:
     * - Misspellings (e.g., "Messy" vs "Messi")
     * - Different languages (e.g., "Μέσι" vs "Messi")
     * - Abbreviations (e.g., "CR7" vs "Cristiano Ronaldo")
     * - Minor variations (e.g., "Real Madrid CF" vs "Real Madrid")
     */
    public function validateAnswer(
        string $question,
        array $correctAnswers,
        string $userAnswer,
        ?float $threshold = null
    ): array {

        // Use config threshold if not provided
        $threshold = $threshold ?? config('quiz.ai_confidence_threshold', 0.85);

        // If AI validation is disabled, only exact match
        if (! config('quiz.ai_validation_enabled', true)) {
            foreach ($correctAnswers as $correct) {
                if (strcasecmp(trim($userAnswer), trim($correct)) === 0) {
                    return [
                        'is_correct' => true,
                        'confidence' => 1.0,
                        'matched_answer' => $correct,
                        'method' => 'exact_match',
                    ];
                }
            }

            return [
                'is_correct' => false,
                'confidence' => 0.0,
                'matched_answer' => null,
                'method' => 'exact_match_failed',
            ];
        }

        // If exact match, no need for AI
        foreach ($correctAnswers as $correct) {
            if (strcasecmp(trim($userAnswer), trim($correct)) === 0) {
                return [
                    'is_correct' => true,
                    'confidence' => 1.0,
                    'matched_answer' => $correct,
                    'method' => 'exact_match',
                ];
            }
        }

        // If OpenAI is not configured, fall back to exact match only
        if (empty(config('openai.api_key'))) {
            return [
                'is_correct' => false,
                'confidence' => 0.0,
                'matched_answer' => null,
                'method' => 'exact_match_failed',
            ];
        }

        try {
            return $this->validateWithAI($question, $correctAnswers, $userAnswer, $threshold);
        } catch (\Exception $e) {
            // If AI fails, fall back to exact match
            \Log::error('AI Answer Validation failed', [
                'error' => $e->getMessage(),
                'question' => $question,
                'user_answer' => $userAnswer,
            ]);

            return [
                'is_correct' => false,
                'confidence' => 0.0,
                'matched_answer' => null,
                'method' => 'ai_failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate answer using OpenAI
     */
    private function validateWithAI(
        string $question,
        array $correctAnswers,
        string $userAnswer,
        float $threshold
    ): array {
        $correctAnswersText = implode(', ', array_map(fn ($a) => "\"$a\"", $correctAnswers));

        $prompt = <<<PROMPT
You are a football quiz answer validator. Your job is to determine if a user's answer is semantically correct, even if it contains:
- Spelling mistakes (e.g., "Messy" instead of "Messi")
- Different languages (e.g., "Μέσι" in Greek instead of "Messi")
- Abbreviations (e.g., "CR7" instead of "Cristiano Ronaldo")
- Minor variations (e.g., "Real Madrid CF" instead of "Real Madrid")

Question: "{$question}"
Correct answers: {$correctAnswersText}
User's answer: "{$userAnswer}"

Analyze if the user's answer is semantically the same as any of the correct answers.

Respond ONLY with a JSON object in this exact format:
{
    "is_correct": true or false,
    "confidence": a number between 0 and 1,
    "matched_answer": "the correct answer it matches" or null,
    "reasoning": "brief explanation"
}
PROMPT;

        $response = OpenAI::chat()->create([
            'model' => config('quiz.ai_model', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => 'You are a precise answer validator. Always respond with valid JSON only.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.1, // Low temperature for consistency
            'max_tokens' => 150,
        ]);

        $content = $response->choices[0]->message->content;

        // Extract JSON from response (in case there's any extra text)
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $content = $matches[0];
        }

        $result = json_decode($content, true);

        if (! $result || ! isset($result['is_correct'])) {
            throw new \Exception('Invalid AI response format');
        }

        // Apply confidence threshold
        $isCorrect = $result['is_correct'] && $result['confidence'] >= $threshold;

        return [
            'is_correct' => $isCorrect,
            'confidence' => $result['confidence'],
            'matched_answer' => $result['matched_answer'] ?? null,
            'reasoning' => $result['reasoning'] ?? null,
            'method' => 'ai_validation',
        ];
    }

    /**
     * Get a user-friendly message for the validation result
     */
    public function getValidationMessage(array $result): string
    {
        if ($result['is_correct']) {
            if ($result['method'] === 'exact_match') {
                return 'Correct!';
            }

            if ($result['confidence'] >= 0.95) {
                return 'Correct! (accepted)';
            }

            return sprintf(
                'Correct! We accepted "%s" as equivalent to "%s"',
                request()->input('answer'),
                $result['matched_answer']
            );
        }

        if ($result['method'] === 'exact_match_failed') {
            return 'Incorrect answer.';
        }

        if (isset($result['reasoning'])) {
            return sprintf('Incorrect. %s', $result['reasoning']);
        }

        return 'Incorrect answer.';
    }
}

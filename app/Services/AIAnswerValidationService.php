<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class AIAnswerValidationService
{
    public function validateAnswer(
        string $question,
        array $correctAnswers,
        string $userAnswer,
        ?float $threshold = null
    ): array {

        $threshold = $threshold ?? config('quiz.ai_confidence_threshold', 0.85);

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

    private function validateWithAI(
        string $question,
        array $correctAnswers,
        string $userAnswer,
        float $threshold
    ): array {
        $correctAnswersText = implode(', ', array_map(fn ($a) => "\"$a\"", $correctAnswers));

        $prompt = <<<PROMPT
You are a football quiz answer validator. Your job is to determine if a user's answer is semantically correct, even if it contains:
- Spelling mistakes (e.g., "Messy" instead of "Messi" or "Ronaldoo" instead of "Ronaldo" or "Σλαταν" instead of "Zlatan" or "Argentine" instead of "Argentina" etc.)
- Different languages (e.g., "Μέσι" in Greek instead of "Messi")
- Shorter Answers (e.g., "Real" for "Real Madrid" or "United or manchester" for "Manchester United", "Barca" for "Barcelona", "Salah" for "Mohamed Salah or Μοχάμεντ Σαλάχ" etc.)
- Abbreviations (e.g., "CR7" instead of "Cristiano Ronaldo")
- if the answer is like 80% similar to the correct answer based on the context of football.
- Minor variations (e.g., "Real Madrid CF" instead of "Real Madrid")
- Synonyms (e.g., "The Red Devils" for "Manchester United")
- Different word order (e.g., "Liverpool FC" vs. "FC Liverpool")
- Αν χρησιμοποιηθεί greeklish αντί για ελληνικά π.χ. "Mesi" αντι για "Μέσι" ή ourougai αντι για ουρουγουάη, salax αντί για Σαλάχ κλπ.

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
            'temperature' => 0.1,
            'max_tokens' => 150,
        ]);

        $content = $response->choices[0]->message->content;

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $content = $matches[0];
        }

        $result = json_decode($content, true);

        if (! $result || ! isset($result['is_correct'])) {
            throw new \Exception('Invalid AI response format');
        }

        $isCorrect = $result['is_correct'] && $result['confidence'] >= $threshold;

        return [
            'is_correct' => $isCorrect,
            'confidence' => $result['confidence'],
            'matched_answer' => $result['matched_answer'] ?? null,
            'reasoning' => $result['reasoning'] ?? null,
            'method' => 'ai_validation',
        ];
    }
}

<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserStatistic;
use Illuminate\Database\Eloquent\Collection;

class StatisticsService
{
    public function updateUserStatistics(User $user, bool $won, int $scoreEarned, Collection $rounds, bool $isDraw = false): void
    {
        $statistics = $user->statistics ?? UserStatistic::create(['user_id' => $user->id]);

        $totalQuestions = $rounds->count();
        $correctAnswers = $rounds->where('is_correct', true)->count();

        $categoryStats = $statistics->category_stats ?? [];

        foreach ($rounds as $round) {
            $categoryId = $round->category_id;

            if (!isset($categoryStats[$categoryId])) {
                $categoryStats[$categoryId] = [
                    'played' => 0,
                    'correct' => 0,
                    'total_score' => 0,
                ];
            }

            $categoryStats[$categoryId]['played']++;
            if ($round->is_correct) {
                $categoryStats[$categoryId]['correct']++;
            }
            $categoryStats[$categoryId]['total_score'] += $round->points_earned;
        }

        $statistics->update([
            'games_played' => $statistics->games_played + 1,
            'games_won' => $statistics->games_won + ($won ? 1 : 0),
            'games_lost' => $statistics->games_lost + (!$won && !$isDraw ? 1 : 0),
            'games_drawn' => $statistics->games_drawn + ($isDraw ? 1 : 0),
            'total_score' => $statistics->total_score + $scoreEarned,
            'total_questions_answered' => $statistics->total_questions_answered + $totalQuestions,
            'correct_answers' => $statistics->correct_answers + $correctAnswers,
            'category_stats' => $categoryStats,
        ]);
    }

    public function getUserStatistics(User $user): array
    {
        $stats = $user->statistics;

        if (!$stats) {
            return $this->getEmptyStatistics();
        }

        return [
            'games_played' => $stats->games_played,
            'games_won' => $stats->games_won,
            'games_lost' => $stats->games_lost,
            'games_drawn' => $stats->games_drawn,
            'win_rate' => $stats->win_rate,
            'total_score' => $stats->total_score,
            'accuracy' => $stats->accuracy,
            'category_performance' => $this->formatCategoryPerformance($stats->category_stats ?? []),
        ];
    }

    protected function getEmptyStatistics(): array
    {
        return [
            'games_played' => 0,
            'games_won' => 0,
            'games_lost' => 0,
            'games_drawn' => 0,
            'win_rate' => 0,
            'total_score' => 0,
            'accuracy' => 0,
            'category_performance' => [],
        ];
    }

    protected function formatCategoryPerformance(array $categoryStats): array
    {
        $formatted = [];

        foreach ($categoryStats as $categoryId => $stats) {
            $accuracy = $stats['played'] > 0
                ? round(($stats['correct'] / $stats['played']) * 100, 2)
                : 0;

            $formatted[] = [
                'category_id' => $categoryId,
                'games_played' => $stats['played'],
                'correct_answers' => $stats['correct'],
                'total_score' => $stats['total_score'],
                'accuracy' => $accuracy,
            ];
        }

        return $formatted;
    }
}
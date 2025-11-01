<?php

namespace App\Services;

use App\Enums\DifficultyLevel;
use App\Enums\GameStatus;
use App\Models\Category;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GameService
{
    public function __construct(
        protected QuestionService   $questionService,
        protected StatisticsService $statisticsService,
    )
    {
    }

    public function createGame(string $gameType, ?User $user = null, ?string $guestName = null, ?string $sessionId = null): Game
    {
        $totalCategories = Category::where('is_active', true)->count();
        $totalDifficulties = 3;
        $maxRounds = $totalCategories * $totalDifficulties;

        $game = Game::create([
            'game_code' => $this->generateGameCode(),
            'status' => GameStatus::Waiting,
            'game_type' => $gameType,
            'current_round' => 0,
            'max_rounds' => $maxRounds,
        ]);

        $player = GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $user?->id,
            'guest_name' => $guestName,
            'session_id' => $sessionId,
            'is_ai' => false,
            'score' => 0,
            'player_order' => 1,
        ]);

        if ($gameType === 'ai') {
            $aiPlayer = GamePlayer::create([
                'game_id' => $game->id,
                'is_ai' => true,
                'score' => 0,
                'player_order' => 2,
            ]);

            $game->update([
                'status' => GameStatus::Active,
                'current_turn_player_id' => $player->id,
                'started_at' => now(),
            ]);

            Cache::put("game:{$game->id}:turn_started_at", now()->timestamp, now()->addMinutes(5));
        }

        $this->cacheGameState($game);

        return $game->fresh(['players']);
    }

    public function joinGame(string $gameCode, ?User $user = null, ?string $guestName = null, ?string $sessionId = null): ?Game
    {
        $game = Game::where('game_code', $gameCode)
            ->where('status', GameStatus::Waiting)
            ->first();

        if (!$game || $game->players()->count() >= 2) {
            return null;
        }

        GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $user?->id,
            'guest_name' => $guestName,
            'session_id' => $sessionId,
            'is_ai' => false,
            'score' => 0,
            'player_order' => 2,
        ]);

        $firstPlayer = $game->players()->orderBy('player_order')->first();

        $game->update([
            'status' => GameStatus::Active,
            'current_turn_player_id' => $firstPlayer->id,
            'started_at' => now(),
        ]);

        Cache::put("game:{$game->id}:turn_started_at", now()->timestamp, now()->addMinutes(5));
        $this->cacheGameState($game);

        return $game->fresh(['players']);
    }

    public function selectCategory(Game $game, GamePlayer $player, Category $category): void
    {
        $activeRound = GameRound::where('game_id', $game->id)
            ->where('game_player_id', $player->id)
            ->whereNull('answered_at')
            ->first();

        if ($activeRound) {
            return;
        }

        Cache::put("game:{$game->id}:selected_category", $category->id, now()->addMinutes(5));
        Cache::put("game:{$game->id}:current_move", [
            'player_id' => $player->id,
            'phase' => 'category',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
            ],
        ], now()->addMinutes(5));
        $this->cacheGameState($game);
    }

    public function getUsedCategoryDifficulties(Game $game): array
    {
        return GameRound::where('game_id', $game->id)
            ->get()
            ->map(fn($round) => [
                'category_id' => $round->category_id,
                'difficulty' => $round->difficulty->value,
            ])
            ->toArray();
    }

    public function isCategoryDifficultyAvailable(Game $game, int $categoryId, string $difficulty): bool
    {
        return !GameRound::where('game_id', $game->id)
            ->where('category_id', $categoryId)
            ->where('difficulty', $difficulty)
            ->exists();
    }

    public function selectDifficulty(Game $game, GamePlayer $player, DifficultyLevel $difficulty): ?Question
    {
        // Check if player has an unanswered question
        $activeRound = GameRound::where('game_id', $game->id)
            ->where('game_player_id', $player->id)
            ->whereNull('answered_at')
            ->first();

        if ($activeRound) {
            // Player must answer current question, return existing question
            $question = Question::find($activeRound->question_id);
            Cache::put("game:{$game->id}:current_round", $activeRound->id, now()->addMinutes(5));

            return $question;
        }

        $categoryId = Cache::get("game:{$game->id}:selected_category");

        if (!$categoryId) {
            return null;
        }

        $category = Category::find($categoryId);

        if (!$category) {
            return null;
        }

        if (!$this->isCategoryDifficultyAvailable($game, $categoryId, $difficulty->value)) {
            return null;
        }

        $excludeCreatorId = $player->user_id;

        $question = $this->questionService->getRandomQuestion($category, $difficulty, $excludeCreatorId);

        if (!$question) {
            return null;
        }

        $game->increment('current_round');

        $round = GameRound::create([
            'game_id' => $game->id,
            'game_player_id' => $player->id,
            'question_id' => $question->id,
            'round_number' => $game->current_round,
            'category_id' => $category->id,
            'difficulty' => $difficulty,
            'started_at' => now(),
        ]);

        Cache::put("game:{$game->id}:current_round", $round->id, now()->addMinutes(5));
        Cache::forget("game:{$game->id}:selected_category");

        // Clear turn_started_at to prevent inactivity forfeit while answering question
        Cache::forget("game:{$game->id}:turn_started_at");

        Cache::put("game:{$game->id}:current_move", [
            'player_id' => $player->id,
            'phase' => 'difficulty',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
            ],
            'difficulty' => $difficulty->value,
            'question' => $question->question_text,
            'started_at' => now()->timestamp,
        ], now()->addMinutes(5));

        $this->cacheGameState($game);

        return $question;
    }

    public function submitAnswer(Game $game, GamePlayer $player, string|array $answer): array
    {
        $roundId = Cache::get("game:{$game->id}:current_round");

        if (!$roundId) {
            return ['success' => false, 'message' => 'No active round found'];
        }

        $round = GameRound::find($roundId);

        if (!$round || $round->game_player_id !== $player->id) {
            return ['success' => false, 'message' => 'Invalid round or player'];
        }

        $question = $round->question()->with('answers')->first();
        $isCorrect = $this->questionService->validateAnswer($question, $answer);
        $pointsEarned = $isCorrect ? $round->difficulty->points() : 0;
        $correctAnswer = $this->getCorrectAnswer($question);
        $playerAnswerDisplay = $this->formatAnswerForDisplay($question, $answer);

        $round->update([
            'player_answer' => is_array($answer) ? json_encode($answer, JSON_UNESCAPED_UNICODE) : $answer,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'answered_at' => now(),
            'time_taken' => abs(now()->diffInSeconds($round->started_at)),
        ]);

        if ($isCorrect) {
            $player->increment('score', $pointsEarned);
        }

        Cache::forget("game:{$game->id}:current_round");

        $currentMove = Cache::get("game:{$game->id}:current_move", []);
        $currentMove['phase'] = 'result';
        $currentMove['answer'] = $playerAnswerDisplay;
        $currentMove['correct_answer'] = $correctAnswer;
        $currentMove['is_correct'] = $isCorrect;
        $currentMove['points_earned'] = $pointsEarned;
        $currentMove['question'] = $question->question_text;
        $currentMove['result_created_at'] = now()->timestamp;
        // Keep result visible for 10 seconds so opponent can see it through polling
        Cache::put("game:{$game->id}:current_move", $currentMove, now()->addSeconds(10));

        $this->switchTurn($game);
        $this->checkGameCompletion($game);
        $this->cacheGameState($game);

        return [
            'success' => true,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'player_answer' => $playerAnswerDisplay,
            'correct_answer' => $correctAnswer,
            'question_text' => $question->question_text,
            'game_status' => $game->fresh()->status,
        ];
    }

    protected function getCorrectAnswer(Question $question): string
    {
        $correctAnswers = $question->answers()->where('is_correct', true)->get();

        if ($question->question_type->value === 'multiple_choice') {
            return $correctAnswers->first()->answer_text;
        }

        if ($question->question_type->value === 'top_5') {
            return $correctAnswers->pluck('answer_text')->take(5)->implode(', ');
        }

        // text_input
        return $correctAnswers->first()->answer_text;
    }

    protected function formatAnswerForDisplay(Question $question, string|array $answer): string
    {
        if ($question->question_type->value === 'multiple_choice') {
            $answerId = $answer;
            $answerModel = $question->answers()->find($answerId);

            return $answerModel ? $answerModel->answer_text : 'No answer';
        }

        if ($question->question_type->value === 'top_5') {
            return is_array($answer) ? implode(', ', $answer) : $answer;
        }

        // text_input
        return $answer;
    }

    protected function switchTurn(Game $game): void
    {
        $players = $game->players()->orderBy('player_order')->get();

        if ($players->count() < 2) {
            return;
        }

        $currentPlayerIndex = $players->search(fn($p) => $p->id === $game->current_turn_player_id);
        $nextPlayerIndex = ($currentPlayerIndex + 1) % $players->count();

        $game->update([
            'current_turn_player_id' => $players[$nextPlayerIndex]->id,
        ]);

        // Don't clear current_move here - let it expire naturally so opponent can see the result
        // phase cache will expire after 10 seconds
        Cache::put("game:{$game->id}:turn_started_at", now()->timestamp, now()->addMinutes(5));
    }

    protected function checkGameCompletion(Game $game): void
    {
        $totalRounds = GameRound::where('game_id', $game->id)->count();

        if ($totalRounds >= $game->max_rounds) {
            $players = $game->players;
            $winner = $players->sortByDesc('score')->first();

            $game->update([
                'status' => GameStatus::Completed,
                'completed_at' => now(),
            ]);

            foreach ($players as $player) {
                if ($player->user_id) {
                    $won = $player->id === $winner->id;
                    $this->statisticsService->updateUserStatistics(
                        $player->user,
                        $won,
                        $player->score,
                        $game->rounds()->where('game_player_id', $player->id)->get()
                    );
                }
            }
        }
    }

    public function forfeitGame(Game $game, GamePlayer $player): void
    {
        $game->update([
            'status' => GameStatus::Completed,
            'completed_at' => now(),
        ]);

        $opponent = $game->players->where('id', '!=', $player->id)->first();
        if ($opponent) {
            $opponent->increment('score', 10);
        }

        Cache::forget("game_state:{$game->id}");
        Cache::forget("game:{$game->id}:selected_category");
        Cache::forget("game:{$game->id}:current_question");
    }

    protected function generateGameCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Game::where('game_code', $code)->exists());

        return $code;
    }

    public function getGameState(Game $game): array
    {
        $usedCombinations = $this->getUsedCategoryDifficulties($game);
        $currentMove = Cache::get("game:{$game->id}:current_move");
        $turnStartedAt = Cache::get("game:{$game->id}:turn_started_at");

        $currentTurnPlayerId = $game->current_turn_player_id;
        if ($currentMove && isset($currentMove['phase']) && $currentMove['phase'] === 'result' && isset($currentMove['result_created_at'])) {
            $elapsed = now()->timestamp - $currentMove['result_created_at'];
            if ($elapsed < 5) {
                $currentTurnPlayerId = $currentMove['player_id'];
            }
        }

        return [
            'id' => $game->id,
            'game_code' => $game->game_code,
            'status' => $game->status->value,
            'current_round' => $game->current_round,
            'max_rounds' => $game->max_rounds,
            'current_turn_player_id' => $currentTurnPlayerId,
            'used_combinations' => $usedCombinations,
            'current_move' => $currentMove,
            'turn_started_at' => $turnStartedAt,
            'players' => $game->players->map(fn($p) => [
                'id' => $p->id,
                'display_name' => $p->display_name,
                'score' => $p->score,
                'is_ai' => $p->is_ai,
            ])->toArray(),
        ];
    }

    protected function cacheGameState(Game $game): void
    {
        $game = $game->fresh(['players']);
        Cache::put("game_state:{$game->id}", $this->getGameState($game), now()->addHours(2));
    }

    public function getCachedGameState(int $gameId): ?array
    {
        return Cache::get("game_state:{$gameId}");
    }
}

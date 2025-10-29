<?php

namespace App\Services;

use App\DifficultyLevel;
use App\GameStatus;
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
        protected QuestionService $questionService,
        protected StatisticsService $statisticsService,
    ) {
    }

    public function createGame(string $gameType, ?User $user = null, ?string $guestName = null, ?string $sessionId = null): Game
    {
        $game = Game::create([
            'game_code' => $this->generateGameCode(),
            'status' => GameStatus::Waiting,
            'game_type' => $gameType,
            'current_round' => 0,
            'max_rounds' => 10,
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

        $this->cacheGameState($game);

        return $game->fresh(['players']);
    }

    public function selectCategory(Game $game, GamePlayer $player, Category $category): void
    {
        Cache::put("game:{$game->id}:selected_category", $category->id, now()->addMinutes(5));
        $this->cacheGameState($game);
    }

    public function selectDifficulty(Game $game, GamePlayer $player, DifficultyLevel $difficulty): ?Question
    {
        $categoryId = Cache::get("game:{$game->id}:selected_category");

        if (!$categoryId) {
            return null;
        }

        $category = Category::find($categoryId);

        if (!$category) {
            return null;
        }

        $question = $this->questionService->getRandomQuestion($category, $difficulty);

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

        $round->update([
            'player_answer' => is_array($answer) ? json_encode($answer) : $answer,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'answered_at' => now(),
            'time_taken' => now()->diffInSeconds($round->started_at),
        ]);

        if ($isCorrect) {
            $player->increment('score', $pointsEarned);
        }

        Cache::forget("game:{$game->id}:current_round");

        $this->switchTurn($game);
        $this->checkGameCompletion($game);
        $this->cacheGameState($game);

        return [
            'success' => true,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'game_status' => $game->fresh()->status,
        ];
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
    }

    protected function checkGameCompletion(Game $game): void
    {
        if ($game->current_round >= $game->max_rounds) {
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
        return [
            'id' => $game->id,
            'game_code' => $game->game_code,
            'status' => $game->status->value,
            'current_round' => $game->current_round,
            'max_rounds' => $game->max_rounds,
            'current_turn_player_id' => $game->current_turn_player_id,
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
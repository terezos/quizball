<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Events\MatchFound;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MatchmakingService
{
    /**
     * Add a player to the matchmaking queue
     */
    public function joinQueue(?User $user, ?string $guestName, string $sessionId, int $gamePace = 6, string $sport = 'football'): Game
    {
        return DB::transaction(function () use ($user, $guestName, $sessionId, $gamePace, $sport) {
            $totalDifficulties = 3;
            $maxRounds = $gamePace * $totalDifficulties;

            // Try to find an existing game in queue matching sport and game_pace (FIFO - oldest first)
            $existingGame = Game::where('status', GameStatus::Waiting)
                ->where('is_matchmaking', true)
                ->where('game_type', 'human')
                ->where('sport', $sport)
                ->where('game_pace', $gamePace)
                ->whereHas('players', function ($query) {
                    $query->havingRaw('COUNT(*) = 1');
                })
                ->orderBy('matchmaking_started_at', 'asc')
                ->lockForUpdate()
                ->first();

            if ($existingGame) {
                $player = GamePlayer::create([
                    'game_id' => $existingGame->id,
                    'user_id' => $user?->id,
                    'guest_name' => $guestName,
                    'session_id' => $sessionId,
                    'is_ai' => false,
                    'score' => 0,
                    'player_order' => 2,
                ]);

                $firstPlayer = $existingGame->players()->orderBy('player_order')->first();

                $existingGame->update([
                    'status' => GameStatus::Active,
                    'started_at' => now(),
                    'current_turn_player_id' => $firstPlayer->id,
                ]);

                $turnStartedAt = now()->timestamp;
                \Illuminate\Support\Facades\Cache::put(
                    "game:{$existingGame->id}:turn_started_at",
                    $turnStartedAt,
                    now()->addMinutes(5)
                );

                $existingGame = $existingGame->fresh(['players']);

                broadcast(new \App\Events\GameStateUpdated($existingGame));
                broadcast(new \App\Events\TurnChanged($existingGame, $firstPlayer->id, $turnStartedAt));

                $matchFoundEvent = new MatchFound(
                    $existingGame,
                    route('game.play', $existingGame)
                );

                broadcast($matchFoundEvent)->toOthers();

                \Log::info('MatchFound event broadcast', [
                    'game_id' => $existingGame->id,
                    'channel' => 'game.' . $existingGame->id,
                    'redirect_url' => route('game.play', $existingGame),
                    'turn_started_at' => $turnStartedAt,
                ]);

                return $existingGame;
            }

            $game = Game::create([
                'game_code' => $this->generateGameCode(),
                'status' => GameStatus::Waiting,
                'game_type' => 'human',
                'is_matchmaking' => true,
                'matchmaking_started_at' => now(),
                'max_rounds' => $maxRounds,
                'game_pace' => $gamePace,
                'sport' => $sport,
            ]);

            $categories = \App\Models\Category::where('is_active', true)
                ->inRandomOrder()
                ->limit($gamePace)
                ->get();

            $game->categories()->attach($categories->pluck('id'));

            GamePlayer::create([
                'game_id' => $game->id,
                'user_id' => $user?->id,
                'guest_name' => $guestName,
                'session_id' => $sessionId,
                'is_ai' => false,
                'score' => 0,
                'player_order' => 1,
            ]);

            return $game->load('players');
        });
    }

    /**
     * Cancel matchmaking for a player
     */
    public function cancelMatchmaking(Game $game): void
    {
        if ($game->is_matchmaking && $game->status === GameStatus::Waiting) {
            $game->delete();
        }
    }

    /**
     * Check if a game has found an opponent
     */
    public function hasFoundOpponent(Game $game): bool
    {
        return $game->players()->count() === 2;
    }

    /**
     * Get queue position (how many games are ahead)
     */
    public function getQueuePosition(Game $game): int
    {
        return Game::where('status', GameStatus::Waiting)
            ->where('is_matchmaking', true)
            ->where('sport', $game->sport)
            ->where('game_pace', $game->game_pace)
            ->where('matchmaking_started_at', '<', $game->matchmaking_started_at)
            ->count() + 1;
    }

    /**
     * Clean up old matchmaking games (older than 5 minutes)
     */
    public function cleanupOldGames(): int
    {
        return Game::where('status', GameStatus::Waiting)
            ->where('is_matchmaking', true)
            ->where('matchmaking_started_at', '<', now()->subMinutes(5))
            ->delete();
    }

    /**
     * Generate a unique game code
     */
    private function generateGameCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (Game::where('game_code', $code)->exists());

        return $code;
    }
}

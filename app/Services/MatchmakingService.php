<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MatchmakingService
{
    /**
     * Add a player to the matchmaking queue
     */
    public function joinQueue(?User $user, ?string $guestName, string $sessionId): Game
    {
        return DB::transaction(function () use ($user, $guestName, $sessionId) {
            // Try to find an existing game in queue (FIFO - oldest first)
            $existingGame = Game::where('status', GameStatus::Waiting)
                ->where('is_matchmaking', true)
                ->where('game_type', 'human')
                ->whereHas('players', function ($query) {
                    $query->havingRaw('COUNT(*) = 1');
                })
                ->orderBy('matchmaking_started_at', 'asc')
                ->lockForUpdate()
                ->first();

            if ($existingGame) {
                // Join existing game
                $player = GamePlayer::create([
                    'game_id' => $existingGame->id,
                    'user_id' => $user?->id,
                    'guest_name' => $guestName,
                    'session_id' => $sessionId,
                    'is_ai' => false,
                    'score' => 0,
                    'joined_at' => now(),
                ]);

                // Start the game
                $existingGame->update([
                    'status' => GameStatus::Active,
                    'started_at' => now(),
                    'current_turn_player_id' => $existingGame->players()->first()->id,
                ]);

                return $existingGame->load('players');
            }

            // Create new game and wait for opponent
            $game = Game::create([
                'game_code' => $this->generateGameCode(),
                'status' => GameStatus::Waiting,
                'game_type' => 'human',
                'is_matchmaking' => true,
                'matchmaking_started_at' => now(),
                'max_rounds' => 24,
            ]);

            GamePlayer::create([
                'game_id' => $game->id,
                'user_id' => $user?->id,
                'guest_name' => $guestName,
                'session_id' => $sessionId,
                'is_ai' => false,
                'score' => 0,
                'joined_at' => now(),
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

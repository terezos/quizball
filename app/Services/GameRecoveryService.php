<?php

namespace App\Services;

use App\GameStatus;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class GameRecoveryService
{
    public function storeActiveGame(Game $game, ?User $user = null, ?string $sessionId = null): void
    {
        if ($user) {
            Session::put('active_game_id', $game->id);
            Session::put('active_game_user_id', $user->id);
        } else {
            Session::put('active_game_id', $game->id);
            Session::put('active_game_session_id', $sessionId ?? Session::getId());
        }

        Session::save();
    }

    public function hasActiveGame(): bool
    {
        return Session::has('active_game_id');
    }

    public function getActiveGame(?User $user = null): ?Game
    {
        $gameId = Session::get('active_game_id');

        if (!$gameId) {
            return null;
        }

        $game = Game::find($gameId);

        if (!$game || $game->status !== GameStatus::Active) {
            $this->clearActiveGame();

            return null;
        }

        $player = $this->findPlayer($game, $user);

        if (!$player) {
            $this->clearActiveGame();

            return null;
        }

        return $game;
    }

    public function getActiveGamePlayer(Game $game, ?User $user = null): ?GamePlayer
    {
        return $this->findPlayer($game, $user);
    }

    protected function findPlayer(Game $game, ?User $user = null): ?GamePlayer
    {
        if ($user) {
            return $game->players()->where('user_id', $user->id)->first();
        }

        $sessionId = Session::get('active_game_session_id') ?? Session::getId();

        return $game->players()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->first();
    }

    public function clearActiveGame(): void
    {
        Session::forget('active_game_id');
        Session::forget('active_game_user_id');
        Session::forget('active_game_session_id');
        Session::save();
    }

    public function isPlayersTurn(Game $game, GamePlayer $player): bool
    {
        return $game->current_turn_player_id === $player->id;
    }
}
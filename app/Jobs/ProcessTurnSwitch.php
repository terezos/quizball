<?php

namespace App\Jobs;

use App\Events\GameStateUpdated;
use App\Models\Game;
use App\Services\AIOpponentService;
use App\Services\GameService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessTurnSwitch implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Game $game,
    )
    {
    }

    public function handle(GameService $gameService, AIOpponentService $aiService): void
    {
        $this->game->refresh(['players']);

        if ($this->game->status->value !== 'active') {
            return;
        }

        $gameService->switchTurn($this->game);
        $gameService->checkGameCompletion($this->game);
        $gameService->cacheGameState($this->game);

        $nextPlayer = $this->game->players->firstWhere('id', $this->game->current_turn_player_id);

        if ($nextPlayer && $nextPlayer->is_ai && $this->game->status->value === 'active') {
            $aiService->playTurn($this->game, $nextPlayer);
        }
    }
}

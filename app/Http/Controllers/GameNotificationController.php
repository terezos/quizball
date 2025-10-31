<?php

namespace App\Http\Controllers;

use App\Services\GameRecoveryService;
use Illuminate\Http\Request;

class GameNotificationController extends Controller
{
    public function __construct(
        protected GameRecoveryService $recoveryService
    ) {}

    public function checkActiveGame(Request $request)
    {
        $activeGame = $this->recoveryService->getActiveGame(auth()->user());

        if (!$activeGame) {
            return response()->json(['hasActiveGame' => false]);
        }

        $player = $this->recoveryService->getActiveGamePlayer($activeGame, auth()->user());

        if (!$player) {
            return response()->json(['hasActiveGame' => false]);
        }

        $startedAt = $activeGame->started_at;
        $elapsedSeconds = $startedAt ? now()->diffInSeconds($startedAt) : 0;
        $remainingSeconds = number_format(120 + $elapsedSeconds, 2);

        return response()->json([
            'hasActiveGame' => true,
            'gameId' => $activeGame->id,
            'gameCode' => $activeGame->game_code,
            'gameUrl' => route('game.play', $activeGame),
            'isYourTurn' => $this->recoveryService->isPlayersTurn($activeGame, $player),
            'currentRound' => $activeGame->current_round,
            'maxRounds' => $activeGame->max_rounds,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }
}

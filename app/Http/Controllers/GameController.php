<?php

namespace App\Http\Controllers;

use App\DifficultyLevel;
use App\Models\Category;
use App\Models\Game;
use App\Services\AIOpponentService;
use App\Services\GameRecoveryService;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GameController extends Controller
{
    public function __construct(
        protected GameService $gameService,
        protected GameRecoveryService $recoveryService,
        protected AIOpponentService $aiService,
        protected \App\Services\MatchmakingService $matchmakingService,
        protected \App\Services\AIAnswerValidationService $aiAnswerValidation,
    ) {}

    public function lobby()
    {
        $activeGame = $this->recoveryService->getActiveGame(auth()->user());

        if ($activeGame) {
            return redirect()->route('game.play', $activeGame);
        }

        return view('game.lobby');
    }

    public function create(Request $request)
    {
        $request->validate([
            'game_type' => 'required|in:ai,human,matchmaking',
            'guest_name' => 'nullable|string|max:255',
        ]);

        // Handle matchmaking
        if ($request->game_type === 'matchmaking') {
            $game = $this->matchmakingService->joinQueue(
                auth()->user(),
                $request->guest_name,
                Session::getId()
            );

            $this->recoveryService->storeActiveGame($game, auth()->user(), Session::getId());

            // If opponent found immediately, go to game
            if ($this->matchmakingService->hasFoundOpponent($game)) {
                return redirect()->route('game.play', $game);
            }

            // Otherwise, wait for opponent
            return redirect()->route('game.matchmaking', $game);
        }

        $game = $this->gameService->createGame(
            $request->game_type,
            auth()->user(),
            $request->guest_name,
            Session::getId()
        );

        $this->recoveryService->storeActiveGame($game, auth()->user(), Session::getId());

        if ($request->game_type === 'human') {
            return redirect()->route('game.wait', $game);
        }

        return redirect()->route('game.play', $game);
    }

    public function join(Request $request)
    {
        $request->validate([
            'game_code' => 'required|string|size:6',
            'guest_name' => 'nullable|string|max:255',
        ]);

        $game = $this->gameService->joinGame(
            strtoupper($request->game_code),
            auth()->user(),
            $request->guest_name,
            Session::getId()
        );

        if (! $game) {
            return back()->withErrors(['game_code' => 'Game not found or already full']);
        }

        $this->recoveryService->storeActiveGame($game, auth()->user(), Session::getId());

        return redirect()->route('game.play', $game);
    }

    public function wait(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return redirect()->route('game.lobby');
        }

        return view('game.wait', [
            'game' => $game->load('players'),
            'player' => $player,
        ]);
    }

    public function matchmaking(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return redirect()->route('game.lobby');
        }

        // If opponent found, redirect to game
        if ($this->matchmakingService->hasFoundOpponent($game->fresh())) {
            return redirect()->route('game.play', $game);
        }

        $queuePosition = $this->matchmakingService->getQueuePosition($game);

        return view('game.matchmaking', [
            'game' => $game->load('players'),
            'player' => $player,
            'queuePosition' => $queuePosition,
        ]);
    }

    public function cancelMatchmaking(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return redirect()->route('game.lobby');
        }

        $this->matchmakingService->cancelMatchmaking($game);
        $this->recoveryService->clearActiveGame(auth()->user(), Session::getId());

        return redirect()->route('game.lobby')->with('success', 'Matchmaking cancelled');
    }

    public function checkMatchmaking(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $game = $game->fresh();
        $hasOpponent = $this->matchmakingService->hasFoundOpponent($game);

        return response()->json([
            'found' => $hasOpponent,
            'queue_position' => $this->matchmakingService->getQueuePosition($game),
            'redirect_url' => $hasOpponent ? route('game.play', $game) : null,
        ]);
    }

    public function play(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return redirect()->route('game.lobby');
        }

        $categories = Category::where('is_active', true)->orderBy('order')->get();
        $isPlayerTurn = $this->recoveryService->isPlayersTurn($game, $player);
        $usedCombinations = $this->gameService->getUsedCategoryDifficulties($game);

        // Check if player has an unanswered question
        $activeRound = \App\Models\GameRound::where('game_id', $game->id)
            ->where('game_player_id', $player->id)
            ->whereNull('answered_at')
            ->with(['question.answers', 'category'])
            ->first();

        return view('game.play', [
            'game' => $game->load('players'),
            'player' => $player,
            'categories' => $categories,
            'isPlayerTurn' => $isPlayerTurn,
            'usedCombinations' => $usedCombinations,
            'activeRound' => $activeRound,
        ]);
    }

    public function selectCategory(Request $request, Game $game)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player || ! $this->recoveryService->isPlayersTurn($game, $player)) {
            return response()->json(['error' => 'Not your turn'], 403);
        }

        $category = Category::findOrFail($request->category_id);
        $this->gameService->selectCategory($game, $player, $category);

        $availableDifficulties = [];
        foreach (['easy', 'medium', 'hard'] as $diff) {
            $availableDifficulties[$diff] = $this->gameService->isCategoryDifficultyAvailable($game, $category->id, $diff);
        }

        return response()->json([
            'success' => true,
            'available_difficulties' => $availableDifficulties,
        ]);
    }

    public function selectDifficulty(Request $request, Game $game)
    {
        $request->validate([
            'difficulty' => 'required|in:easy,medium,hard',
        ]);

        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player || ! $this->recoveryService->isPlayersTurn($game, $player)) {
            return response()->json(['error' => 'Not your turn'], 403);
        }

        $difficulty = DifficultyLevel::from($request->difficulty);
        $question = $this->gameService->selectDifficulty($game, $player, $difficulty);

        if (! $question) {
            return response()->json(['error' => 'No questions available'], 400);
        }

        return response()->json([
            'success' => true,
            'question' => [
                'id' => $question->id,
                'text' => $question->question_text,
                'type' => $question->question_type->value,
                'difficulty' => $question->difficulty->value,
                'answers' => $question->question_type->value === 'multiple_choice'
                    ? $question->answers->map(fn ($a) => [
                        'id' => $a->id,
                        'text' => $a->answer_text,
                    ])
                    : null,
            ],
        ]);
    }

    public function submitAnswer(Request $request, Game $game)
    {
        $request->validate([
            'answer' => 'required',
        ]);

        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return response()->json(['error' => 'Invalid player'], 403);
        }

        $result = $this->gameService->submitAnswer($game, $player, $request->answer);

        // If next player is AI, trigger AI turn
        $game = $game->fresh(['players']);
        $nextPlayer = $game->players->firstWhere('id', $game->current_turn_player_id);

        if ($nextPlayer && $nextPlayer->is_ai && $game->status->value === 'active') {
            dispatch(function () use ($game, $nextPlayer) {
                $this->aiService->playTurn($game, $nextPlayer);
            })->afterResponse();
        }

        return response()->json($result);
    }

    public function getState(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return response()->json(['error' => 'Invalid player'], 403);
        }

        $gameState = $this->gameService->getGameState($game->fresh(['players']));

        // Check for inactivity timeout (2 minutes = 120 seconds)
        if ($gameState['turn_started_at'] && $gameState['status'] === 'active') {
            $elapsed = now()->timestamp - $gameState['turn_started_at'];
            if ($elapsed >= 120) {
                $currentPlayer = $game->players->firstWhere('id', $game->current_turn_player_id);
                if ($currentPlayer) {
                    $this->gameService->forfeitGame($game, $currentPlayer);
                    $gameState = $this->gameService->getGameState($game->fresh(['players']));
                    $gameState['auto_forfeit'] = true;
                }
            }
        }

        return response()->json($gameState);
    }

    public function forfeit(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return response()->json(['error' => 'Invalid player'], 403);
        }

        $this->gameService->forfeitGame($game, $player);
        $this->recoveryService->clearActiveGame();

        return response()->json([
            'success' => true,
            'redirect_url' => route('game.results', $game),
        ]);
    }

    public function results(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return redirect()->route('game.lobby');
        }

        $this->recoveryService->clearActiveGame();

        $winner = $game->players()->orderByDesc('score')->first();

        return view('game.results', [
            'game' => $game->load(['players', 'rounds.question', 'rounds.category']),
            'player' => $player,
            'winner' => $winner,
        ]);
    }
}

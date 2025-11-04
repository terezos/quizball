<?php

namespace App\Http\Controllers;

use App\Enums\DifficultyLevel;
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

        $categoriesCount = Category::where('is_active', true)->count();
        $questionsCount = \App\Models\Question::count();

        return view('game.lobby', [
            'categoriesCount' => $categoriesCount,
            'questionsCount' => $questionsCount,
        ]);
    }

    public function create(Request $request)
    {
        $validationRules = [
            'game_type' => 'required|in:ai,human,matchmaking',
            'guest_name' => 'nullable|string|max:255',
        ];

        // Only require pace and sport for non-matchmaking games
        if ($request->game_type !== 'matchmaking') {
            $validationRules['game_pace'] = 'required|in:4,6,8';
            $validationRules['sport'] = 'required|in:football,basketball';
        }

        // AI difficulty only for AI games
        if ($request->game_type === 'ai') {
            $validationRules['ai_difficulty'] = 'required|in:1,2,3';
        }

        $request->validate($validationRules);

        // Check if private game requires authentication
        if ($request->game_type === 'human' && auth()->guest()) {
            return back()->with('error', 'Πρέπει να συνδεθείτε για να δημιουργήσετε ιδιωτικό παιχνίδι.');
        }

        // Check daily limit for non-premium users creating private games
        if ($request->game_type === 'human' && auth()->check() && !auth()->user()->isPremium()) {
            $todayGamesCount = \App\Models\Game::where('game_type', 'human')
                ->whereHas('gamePlayers', function($q) {
                    $q->where('user_id', auth()->id());
                })
                ->whereDate('created_at', today())
                ->count();

            if ($todayGamesCount >= 2) {
                return back()->with('error', 'Έχετε φτάσει το όριο των 2 ιδιωτικών παιχνιδιών για σήμερα. Κάντε αναβάθμιση σε Premium για απεριόριστα παιχνίδια!');
            }
        }

        // Handle matchmaking (uses default pace and sport for now)
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
            Session::getId(),
            (int) $request->game_pace,
            $request->sport,
            (int) $request->input('ai_difficulty', 2)
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

        // Find the game first to check if it's a private game
        $game = \App\Models\Game::where('game_code', strtoupper($request->game_code))->first();

        if (!$game) {
            return back()->withErrors(['game_code' => 'Ο κωδικός παιχνιδιού δεν βρέθηκε']);
        }

        // Check if it's a private (human) game and user is guest
        if ($game->game_type === 'human' && auth()->guest()) {
            return back()->withErrors(['game_code' => 'Πρέπει να συνδεθείτε για να συμμετάσχετε σε ιδιωτικό παιχνίδι']);
        }

        // Check daily limit for non-premium users joining private games
        if ($game->game_type === 'human' && auth()->check() && !auth()->user()->isPremium()) {
            $todayGamesCount = \App\Models\Game::where('game_type', 'human')
                ->whereHas('gamePlayers', function($q) {
                    $q->where('user_id', auth()->id());
                })
                ->whereDate('created_at', today())
                ->count();

            if ($todayGamesCount >= 2) {
                return back()->withErrors(['game_code' => 'Έχετε φτάσει το όριο των 2 ιδιωτικών παιχνιδιών για σήμερα. Κάντε αναβάθμιση σε Premium για απεριόριστα παιχνίδια!']);
            }
        }

        $game = $this->gameService->joinGame(
            strtoupper($request->game_code),
            auth()->user(),
            $request->guest_name,
            Session::getId()
        );

        if (! $game) {
            return back()->withErrors(['game_code' => 'Το παιχνίδι δεν βρέθηκε ή είναι γεμάτο']);
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

//        $queuePosition = $this->matchmakingService->getQueuePosition($game);

        return response()
            ->view('game.matchmaking', [
                'game' => $game->load('players'),
                'player' => $player,
//                'queuePosition' => $queuePosition,
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
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

        $categories = $game->categories()->orderBy('order')->get();
        $isPlayerTurn = $this->recoveryService->isPlayersTurn($game, $player);
        $usedCombinations = $this->gameService->getUsedCategoryDifficulties($game);

        $activeRound = \App\Models\GameRound::where('game_id', $game->id)
            ->where('game_player_id', $player->id)
            ->whereNull('answered_at')
            ->with(['question.answers', 'category', 'question.creator'])
            ->first();

        $turnStartedAt = \Illuminate\Support\Facades\Cache::get("game:{$game->id}:turn_started_at");

        return view('game.play', [
            'game' => $game->load('players'),
            'player' => $player,
            'categories' => $categories,
            'isPlayerTurn' => $isPlayerTurn,
            'usedCombinations' => $usedCombinations,
            'activeRound' => $activeRound,
            'turnStartedAt' => $turnStartedAt,
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
                'created_by' => $question->creator->name,
                'image_url' => $question->image_url,
                'answers' => $question->question_type->value === 'multiple_choice'
                    ? $question->answers->map(fn ($a) => [
                        'id' => $a->id,
                        'answer_text' => $a->answer_text,
                    ])
                    : null,
            ],
        ]);
    }

    public function submitAnswer(Request $request, Game $game)
    {
        $request->validate([
            'answer' => 'nullable', // Allow empty answer for timeout scenarios
        ]);

        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return response()->json(['error' => 'Invalid player'], 403);
        }

        $answer = $request->answer ?? '';

        $result = $this->gameService->submitAnswer($game, $player, $answer);


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

    public function getRounds(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (! $player) {
            return response()->json(['error' => 'Invalid player'], 403);
        }

        $rounds = $game->rounds()
            ->with(['category:id,name,icon', 'gamePlayer', 'question.answers'])
            ->orderBy('round_number', 'asc')
            ->get()
            ->map(function ($round) {
                $questionData = null;
                $playerAnswerText = null;
                $correctAnswerText = null;

                if ($round->question) {
                    $questionData = [
                        'id' => $round->question->id,
                        'text' => $round->question->question_text,
                        'type' => $round->question->question_type->value,
                    ];

                    // Get correct answer(s)
                    $correctAnswers = $round->question->answers->where('is_correct', true);
                    if ($round->question->question_type->value === 'top_5') {
                        $correctAnswerText = $correctAnswers->take(5)->pluck('answer_text')->implode(', ');
                    } else {
                        $correctAnswerText = $correctAnswers->first()?->answer_text;
                    }

                    // Get player's answer text
                    if ($round->player_answer) {
                        if ($round->question->question_type->value === 'multiple_choice') {
                            $answerId = is_numeric($round->player_answer) ? $round->player_answer : json_decode($round->player_answer, true);
                            $playerAnswerText = $round->question->answers->find($answerId)?->answer_text ?? 'No answer';
                        } elseif ($round->question->question_type->value === 'top_5') {
                            $answers = json_decode($round->player_answer, true);
                            $playerAnswerText = is_array($answers) ? implode(', ', $answers) : $round->player_answer;
                        } else {
                            $playerAnswerText = $round->player_answer;
                        }
                    }
                }

                return [
                    'id' => $round->id,
                    'round_number' => $round->round_number,
                    'category' => $round->category ? [
                        'id' => $round->category->id,
                        'name' => $round->category->name,
                        'icon' => $round->category->icon,
                    ] : null,
                    'difficulty' => $round->difficulty ? [
                        'value' => $round->difficulty->value,
                        'label' => $round->difficulty->label(),
                    ] : null,
                    'points_earned' => $round->points_earned ?? 0,
                    'is_correct' => $round->is_correct ?? false,
                    'time_taken' => $round->time_taken,
                    'question' => $questionData,
                    'player_answer' => $playerAnswerText,
                    'correct_answer' => $correctAnswerText,
                ];
            });

        return response()->json(['rounds' => $rounds]);
    }

    public function forfeit(Game $game)
    {
        $player = $this->recoveryService->getActiveGamePlayer($game, auth()->user());

        if (!$player) {
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

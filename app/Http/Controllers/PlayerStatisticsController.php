<?php

namespace App\Http\Controllers;

use App\Models\GamePlayer;
use App\Models\GameRound;
use Illuminate\Http\Request;

class PlayerStatisticsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $completedGames = GamePlayer::where('user_id', $user->id)
            ->whereHas('game', function($q) {
                $q->where('status', 'completed');
            })
            ->with('game.gamePlayers')
            ->get();

        $totalGames = $completedGames->count();
        $gamesWon = $completedGames->filter(function($gp) {
            return $gp->is_winner;
        })->count();
        $gamesLost = $totalGames - $gamesWon;
        $winRate = $totalGames > 0 ? round(($gamesWon / $totalGames) * 100) : 0;

        $totalScore = GamePlayer::where('user_id', $user->id)->sum('score');

        $correctAnswers = \DB::table('game_rounds')
            ->join('game_players', 'game_rounds.game_player_id', '=', 'game_players.id')
            ->where('game_players.user_id', $user->id)
            ->where('game_rounds.is_correct', true)
            ->count();

        $totalAnswers = \DB::table('game_rounds')
            ->join('game_players', 'game_rounds.game_player_id', '=', 'game_players.id')
            ->where('game_players.user_id', $user->id)
            ->count();

        $incorrectAnswers = $totalAnswers - $correctAnswers;
        $accuracy = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100) : 0;

        $statistics = $user->statistics;

        $recentGames = GamePlayer::where('user_id', $user->id)
            ->with(['game.rounds.category', 'game.gamePlayers.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $gamesVsAI = GamePlayer::where('game_players.user_id', $user->id)
            ->join('games', 'game_players.game_id', '=', 'games.id')
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('game_players as gp_ai')
                    ->whereColumn('gp_ai.game_id', 'games.id')
                    ->where('gp_ai.is_ai', true);
            })
            ->where('games.status', 'completed')
            ->count();

        $winsVsAI = GamePlayer::where('game_players.user_id', $user->id)
            ->join('games', 'game_players.game_id', '=', 'games.id')
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('game_players as gp_ai')
                    ->whereColumn('gp_ai.game_id', 'games.id')
                    ->where('gp_ai.is_ai', true);
            })
            ->where('games.status', 'completed')
            ->whereRaw('game_players.score > (
                SELECT gp2.score
                FROM game_players gp2
                WHERE gp2.game_id = game_players.game_id
                AND gp2.id != game_players.id
                LIMIT 1
            )')
            ->count();

        $gamesVsHumans = GamePlayer::where('game_players.user_id', $user->id)
            ->join('games', 'game_players.game_id', '=', 'games.id')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('game_players as gp_ai')
                    ->whereColumn('gp_ai.game_id', 'games.id')
                    ->where('gp_ai.is_ai', true);
            })
            ->where('games.status', 'completed')
            ->count();

        $winsVsHumans = GamePlayer::where('game_players.user_id', $user->id)
            ->join('games', 'game_players.game_id', '=', 'games.id')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('game_players as gp_ai')
                    ->whereColumn('gp_ai.game_id', 'games.id')
                    ->where('gp_ai.is_ai', true);
            })
            ->where('games.status', 'completed')
            ->whereRaw('game_players.score > (
                SELECT gp2.score
                FROM game_players gp2
                WHERE gp2.game_id = game_players.game_id
                AND gp2.id != game_players.id
                LIMIT 1
            )')
            ->count();

        $bestCategories = \DB::table('game_players')
            ->join('games', 'game_players.game_id', '=', 'games.id')
            ->join('game_rounds', 'games.id', '=', 'game_rounds.game_id')
            ->join('categories', 'game_rounds.category_id', '=', 'categories.id')
            ->where('game_players.user_id', $user->id)
            ->where('games.status', 'completed')
            ->whereRaw('game_players.score > (
                SELECT gp2.score
                FROM game_players gp2
                WHERE gp2.game_id = game_players.game_id
                AND gp2.id != game_players.id
                LIMIT 1
            )')
            ->select('categories.name', 'categories.icon', \DB::raw('count(DISTINCT games.id) as wins'))
            ->groupBy('categories.id', 'categories.name', 'categories.icon')
            ->orderBy('wins', 'desc')
            ->limit(5)
            ->get();

        $recentOpponents = GamePlayer::where('game_players.user_id', '!=', $user->id)
            ->where('game_players.is_ai', false)
            ->join('games', 'game_players.game_id', '=', 'games.id')
            ->whereExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                    ->from('game_players as gp2')
                    ->whereColumn('gp2.game_id', 'games.id')
                    ->where('gp2.user_id', $user->id);
            })
            ->join('users', 'game_players.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', \DB::raw('count(*) as games_count'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('games_count', 'desc')
            ->limit(5)
            ->get();

        return view('statistics.index', [
            'statistics' => $statistics,
            'recentGames' => $recentGames,
            'totalGames' => $totalGames,
            'gamesWon' => $gamesWon,
            'gamesLost' => $gamesLost,
            'winRate' => $winRate,
            'totalScore' => $totalScore,
            'correctAnswers' => $correctAnswers,
            'incorrectAnswers' => $incorrectAnswers,
            'accuracy' => $accuracy,
            'gamesVsAI' => $gamesVsAI,
            'winsVsAI' => $winsVsAI,
            'gamesVsHumans' => $gamesVsHumans,
            'winsVsHumans' => $winsVsHumans,
            'bestCategories' => $bestCategories,
            'recentOpponents' => $recentOpponents,
        ]);
    }
}

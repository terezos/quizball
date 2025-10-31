<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $query = User::with(['statistics'])
            ->withCount(['questions', 'gamePlayers']);

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->only(['role', 'search']));

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $user->load([
            'questions' => fn($q) => $q->latest()->take(10),
            'statistics',
            'gamePlayers' => fn($q) => $q->with('game')->latest()->take(10)
        ]);
        
        // Get total counts
        $totalQuestionsCount = $user->questions()->count();
        $totalGamesCount = $user->gamePlayers()->count();
        
        // Calculate game statistics
        $completedGames = $user->gamePlayers()
            ->whereHas('game', function($q) {
                $q->where('status', 'completed');
            })
            ->with('game.gamePlayers')
            ->get();
        
        $gamesPlayed = $completedGames->count();
        $gamesWon = $completedGames->filter(function($gp) {
            return $gp->is_winner;
        })->count();
        $gamesLost = $gamesPlayed - $gamesWon;
        $winRate = $gamesPlayed > 0 ? round(($gamesWon / $gamesPlayed) * 100) : 0;
        
        // Calculate total score
        $totalScore = $user->gamePlayers()->sum('score');

        return view('admin.users.show', [
            'user' => $user,
            'totalQuestionsCount' => $totalQuestionsCount,
            'totalGamesCount' => $totalGamesCount,
            'gamesPlayed' => $gamesPlayed,
            'gamesWon' => $gamesWon,
            'gamesLost' => $gamesLost,
            'winRate' => $winRate,
            'totalScore' => $totalScore,
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $request->validate([
            'role' => 'required|in:user,editor,admin',
        ]);

        $user->update([
            'role' => $request->role,
        ]);

        return back()->with('success', 'User role updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return back()->with('success', 'User status updated successfully.');
    }

    public function destroy(User $user)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}

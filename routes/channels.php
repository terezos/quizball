<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Game;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('game.{gameId}', function ($user, $gameId) {
    $game = Game::find($gameId);

    if (!$game) {
        return false;
    }

    $sessionId = session()->getId();

    return $game->players()
        ->where(function($query) use ($user, $sessionId) {
            $query->where('session_id', $sessionId);
            if ($user) {
                $query->orWhere('user_id', $user->id);
            }
        })
        ->exists();
});

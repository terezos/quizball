<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStateUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Game $game
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            //            new PrivateChannel('game.' . $this->game->id),
            new Channel('game.' . $this->game->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'game' => [
                'id' => $this->game->id,
                'status' => $this->game->status->value,
                'current_round' => $this->game->current_round,
                'max_rounds' => $this->game->max_rounds,
                'current_turn_player_id' => $this->game->current_turn_player_id,
                'turn_started_at' => $this->game->turn_started_at ?? \Illuminate\Support\Facades\Cache::get("game:{$this->game->id}:turn_started_at"),
                'used_combinations' => \App\Models\GameRound::where('game_id', $this->game->id)
                    ->get()
                    ->map(fn($round) => [
                        'category_id' => $round->category_id,
                        'difficulty' => $round->difficulty->value,
                    ])
                    ->toArray(),
                'players' => $this->game->players->map(fn($player) => [
                    'id' => $player->id,
                    'user_id' => $player->user_id,
                    'guest_name' => $player->guest_name,
                    'display_name' => $player->display_name,
                    'score' => $player->score,
                    'is_ai' => $player->is_ai,
                    'player_order' => $player->player_order,
                ])->toArray(),
            ],
        ];
    }
}

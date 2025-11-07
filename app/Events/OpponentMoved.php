<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OpponentMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Game $game,
        public array $move
    ) {}

    public function broadcastOn(): array
    {
        return [
            //            new PrivateChannel('game.' . $this->game->id),
            new Channel('game.'.$this->game->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'move' => $this->move,
        ];
    }
}

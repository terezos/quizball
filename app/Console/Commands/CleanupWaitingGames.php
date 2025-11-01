<?php

namespace App\Console\Commands;

use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CleanupWaitingGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:cleanup-waiting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete games that have been in waiting status for more than 2 hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cleanup of old waiting games...');

        $twoHoursAgo = Carbon::now()->subHours(2);

        $oldWaitingGames = Game::where('status', 'waiting')
            ->where('created_at', '<=', $twoHoursAgo)
            ->get();

        if ($oldWaitingGames->isEmpty()) {
            $this->info('No old waiting games found.');
            return CommandAlias::SUCCESS;
        }

        $count = $oldWaitingGames->count();
        $this->warn("Found {$count} game(s) in waiting status for more than 2 hours.");

        foreach ($oldWaitingGames as $game) {
            $this->line("Deleting game #{$game->id} (created at {$game->created_at})");

            $game->gamePlayers()->delete();

            $game->delete();
        }

        $this->info("Successfully deleted {$count} old waiting game(s).");

        return CommandAlias::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Games table indexes
        Schema::table('games', function (Blueprint $table) {
            // Matchmaking queries: WHERE status = 'waiting' AND is_matchmaking = 1 AND game_type = 'human' AND sport = '...' AND game_pace = ...
            $table->index(['status', 'is_matchmaking', 'game_type', 'sport', 'game_pace'], 'games_matchmaking_idx');

            // Active games queries: WHERE status = 'active'
            $table->index(['status', 'created_at'], 'games_status_created_idx');

            // Game code lookups: WHERE game_code = '...'
            $table->index('game_code');

            // Current turn lookups
            $table->index('current_turn_player_id');
        });

        // Game rounds table indexes
        Schema::table('game_rounds', function (Blueprint $table) {
            // Most common: WHERE game_id = ... AND game_player_id = ... AND answered_at IS NULL
            $table->index(['game_id', 'game_player_id', 'answered_at'], 'game_rounds_active_idx');

            // Category/difficulty checks: WHERE game_id = ... AND category_id = ... AND difficulty = ...
            $table->index(['game_id', 'category_id', 'difficulty'], 'game_rounds_combo_idx');

            // Powerup checks: WHERE game_id = ... AND game_player_id = ... AND used_2x_powerup = 1
            $table->index(['game_id', 'game_player_id', 'used_2x_powerup'], 'game_rounds_powerup_idx');

            // Question lookups
            $table->index('question_id');
        });

        // Game players table indexes
        Schema::table('game_players', function (Blueprint $table) {
            // User game lookups: WHERE user_id = ... AND game_id = ...
            $table->index(['user_id', 'game_id'], 'game_players_user_game_idx');

            // Session lookups for guests
            $table->index('session_id');

            // Score ordering: ORDER BY score DESC
            $table->index('score');
        });

        // Questions table indexes
        Schema::table('questions', function (Blueprint $table) {
            // Random question selection: WHERE category_id = ... AND difficulty = ... AND is_active = 1 AND status = 'approved'
            $table->index(['category_id', 'difficulty', 'is_active', 'status'], 'questions_selection_idx');

            // Creator exclusion: WHERE created_by != ...
            $table->index('created_by');

            // Status filtering
            $table->index(['status', 'created_at'], 'questions_status_idx');
        });

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            // Active category selection: WHERE is_active = 1 AND sport = '...'
            $table->index(['is_active', 'sport', 'order'], 'categories_active_sport_idx');
        });

        // Game category pivot table indexes
        Schema::table('game_category', function (Blueprint $table) {
            // Category to games lookup
            $table->index('category_id');
            $table->index('game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex('games_matchmaking_idx');
            $table->dropIndex('games_status_created_idx');
            $table->dropIndex(['game_code']);
            $table->dropIndex(['current_turn_player_id']);
        });

        Schema::table('game_rounds', function (Blueprint $table) {
            $table->dropIndex('game_rounds_active_idx');
            $table->dropIndex('game_rounds_combo_idx');
            $table->dropIndex('game_rounds_powerup_idx');
            $table->dropIndex(['question_id']);
        });

        Schema::table('game_players', function (Blueprint $table) {
            $table->dropIndex('game_players_user_game_idx');
            $table->dropIndex(['session_id']);
            $table->dropIndex(['score']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('questions_selection_idx');
            $table->dropIndex(['created_by']);
            $table->dropIndex('questions_status_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_active_sport_idx');
        });

        Schema::table('game_category', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['game_id']);
        });
    }
};

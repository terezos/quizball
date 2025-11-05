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
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_forfeited')->default(false)->after('completed_at');
            $table->foreignId('forfeited_by_player_id')->nullable()->after('is_forfeited')->constrained('game_players')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['forfeited_by_player_id']);
            $table->dropColumn(['is_forfeited', 'forfeited_by_player_id']);
        });
    }
};

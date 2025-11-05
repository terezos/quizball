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
        Schema::table('game_rounds', function (Blueprint $table) {
            $table->boolean('used_5050_powerup')->default(false)->after('used_2x_powerup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_rounds', function (Blueprint $table) {
            $table->dropColumn('used_5050_powerup');
        });
    }
};

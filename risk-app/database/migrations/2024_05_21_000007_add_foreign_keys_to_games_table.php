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
            $table->foreign('current_turn_player_id')->references('id')->on('players')->onDelete('set null');
            $table->foreign('winner_player_id')->references('id')->on('players')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['current_turn_player_id']);
            $table->dropForeign(['winner_player_id']);
        });
    }
};

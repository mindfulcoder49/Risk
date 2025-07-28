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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->foreignId('current_turn_player_id')->nullable();
            $table->enum('turn_phase', ['reinforce', 'attack', 'fortify', 'claim'])->nullable();
            $table->foreignId('winner_player_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};

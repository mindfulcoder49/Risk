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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('color'); // e.g., '#FF0000'
            $table->integer('turn_order');
            $table->enum('status', ['active', 'eliminated'])->default('active');
            $table->unique(['game_id', 'user_id']); // User can only join a game once
            $table->unique(['game_id', 'turn_order']); // Turn order must be unique within a game
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};

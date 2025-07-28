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
        Schema::create('territory_adjacency', function (Blueprint $table) {
            $table->foreignId('territory_id')->constrained()->onDelete('cascade');
            $table->foreignId('adjacent_territory_id')->constrained('territories')->onDelete('cascade');
            $table->primary(['territory_id', 'adjacent_territory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territory_adjacency');
    }
};

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
        Schema::create('odds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->dateTime('date_time')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->string('source')->nullable();
            $table->decimal('home_win_odds', 6, 2, true)->nullable();
            $table->decimal('draw_odds', 6, 2, true)->nullable();
            $table->decimal('away_win_odds', 6, 2, true)->nullable();
            $table->decimal('over_odds', 6, 2, true)->nullable();
            $table->decimal('under_odds', 6, 2, true)->nullable();
            $table->decimal('gg_odds', 6, 2, true)->nullable();
            $table->decimal('ng_odds', 6, 2, true)->nullable();
            $table->uuid('game_id')->nullable();
            $table->uuid('user_id');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odds');
    }
};

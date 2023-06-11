<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('name');
            $table->string('slug');
            $table->string('url');
            $table->foreignId('competition_id');
            $table->foreignId('country_id');
            $table->string('img')->nullable();
            $table->foreignId('user_id');
            $table->string('status');
            $table->dateTime('last_fetch')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};

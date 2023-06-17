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
        Schema::create('competitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('icon')->nullable();
            $table->string('country_id');
            $table->string('url')->nullable();
            $table->string('img')->nullable();
            $table->integer('priority')->default(0);
            $table->string('status');
            $table->boolean('is_domestic')->nullable();
            $table->dateTime('last_fetch')->nullable();
            $table->uuid('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};

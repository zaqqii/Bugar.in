<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Points history - log of every point earned by a user
     */
    public function up(): void
    {
        Schema::create('points_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points'); // can be negative for deductions
            $table->enum('source', ['attendance', 'streak_bonus', 'event_participation', 'referral', 'manual', 'other'])->default('attendance');
            $table->morphs('pointable'); // polymorphic source (attendance, event, etc.)
            $table->text('description')->nullable();
            $table->integer('balance_after'); // running total after this transaction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_histories');
    }
};

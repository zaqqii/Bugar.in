<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Events - incidental events organized by community (tournament, fun match, gathering)
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('type', ['fun_match', 'tournament', 'gathering', 'workshop', 'other'])->default('other');
            $table->string('location');
            $table->string('maps_url')->nullable();
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->integer('capacity')->nullable(); // null = unlimited
            $table->decimal('registration_fee', 10, 2)->default(0.00); // 0 = free
            $table->text('requirements')->nullable();
            $table->text('prizes')->nullable(); // prize info for tournaments
            $table->timestamp('registration_open_at')->nullable();
            $table->timestamp('registration_close_at')->nullable();
            $table->enum('status', ['draft', 'published', 'registration_closed', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->integer('participants_count')->default(0);
            $table->integer('points_reward')->default(0); // points awarded on attendance
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

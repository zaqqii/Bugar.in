<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Participants - registration records for schedules or events (polymorphic)
     */
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Polymorphic: can belong to schedule OR event
            $table->morphs('participatable'); // participatable_id + participatable_type
            $table->enum('status', ['registered', 'waiting_list', 'cancelled', 'attended', 'no_show'])->default('registered');
            $table->string('qr_code')->unique()->nullable(); // unique QR for check-in
            $table->string('notes')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'participatable_id', 'participatable_type'], 'unique_participant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};

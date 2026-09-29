<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Communities - sport communities managed by community owners
     */
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('sport_category'); // e.g. running, basket, badminton, etc.
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->integer('member_limit')->nullable(); // null = unlimited
            $table->integer('followers_count')->default(0);
            $table->integer('members_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('ratings_count')->default(0);
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true); // public = anyone can see, private = need join request
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table: user follows community (not member, just follower)
        Schema::create('community_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->timestamp('followed_at')->useCurrent();
            $table->unique(['user_id', 'community_id']);
        });

        // Pivot table: user joins community as member
        Schema::create('community_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'active', 'banned', 'left'])->default('active');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->unique(['user_id', 'community_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_members');
        Schema::dropIfExists('community_followers');
        Schema::dropIfExists('communities');
    }
};

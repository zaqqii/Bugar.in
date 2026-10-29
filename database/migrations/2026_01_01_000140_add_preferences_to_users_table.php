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
        Schema::table('users', function (Blueprint $table) {
            $table->json('sports')->nullable()->after('bio');
            $table->string('fitness_level')->nullable()->default('intermediate')->after('sports');
            $table->string('weekly_target')->nullable()->default('3-4')->after('fitness_level');
            $table->boolean('sparring_open')->default(true)->after('weekly_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sports', 'fitness_level', 'weekly_target', 'sparring_open']);
        });
    }
};

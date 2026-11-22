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
        Schema::table('reports', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable();
            $table->foreignId('community_id')->nullable()->index()->constrained('communities')->nullOnDelete();
            $table->text('action_note')->nullable();
            $table->json('evidence_images')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['community_id']);
            $table->dropColumn([
                'rejection_reason',
                'community_id',
                'action_note',
                'evidence_images',
            ]);
        });
    }
};

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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->enum('purpose', ['event_registration', 'community_verification']);
            $table->enum('method', ['virtual_account', 'qris']);
            $table->enum('status', ['awaiting_proof', 'proof_uploaded', 'approved', 'rejected', 'expired'])->default('awaiting_proof');
            // FK nullable — index dibuat terpisah agar nama constraint tidak collide di PostgreSQL
            $table->foreignId('payer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->foreignId('community_id')->nullable()->constrained('communities')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('destination_bank')->nullable();
            $table->string('destination_account')->nullable();
            $table->string('destination_name')->nullable();
            $table->string('destination_qris')->nullable();
            $table->string('proof_path')->nullable();
            $table->timestamp('proof_uploaded_at')->nullable();
            $table->unsignedSmallInteger('proof_attempts')->default(0);
            $table->string('sender_name')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Index terpisah — nama dihasilkan secara deterministik oleh Blueprint
            $table->index('payer_id');
            $table->index('participant_id');
            $table->index('community_id');
            $table->index(['purpose', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

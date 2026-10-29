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
        Schema::table('communities', function (Blueprint $table) {
            $table->string('verification_type')->nullable()->after('verification_status');
            $table->decimal('verification_fee', 10, 2)->nullable()->after('verification_type');
            $table->string('verification_payment_method')->nullable()->after('verification_fee');
            $table->timestamp('verification_requested_at')->nullable()->after('verification_payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn([
                'verification_type',
                'verification_fee',
                'verification_payment_method',
                'verification_requested_at',
            ]);
        });
    }
};

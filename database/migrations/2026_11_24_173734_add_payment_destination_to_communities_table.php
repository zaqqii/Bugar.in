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
            $table->string('payment_bank')->nullable();
            $table->string('payment_account')->nullable();
            $table->string('payment_account_name')->nullable();
            $table->string('payment_qris_image')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('payment_account_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('payment_account_name');
        });

        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn([
                'payment_bank',
                'payment_account',
                'payment_account_name',
                'payment_qris_image',
            ]);
        });
    }
};

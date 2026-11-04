<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration ini opsional (perbaikan data lama):
 * Menormalkan status peserta jadwal yang tersimpan sebagai 'waiting_list'
 * menjadi 'registered', karena jadwal tidak lagi menggunakan waiting list.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('participants')
            ->where('participatable_type', 'App\\Models\\Schedule')
            ->where('status', 'waiting_list')
            ->update(['status' => 'registered']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // down: tidak melakukan apa-apa karena tidak dapat membedakan
        // record mana yang asalnya waiting_list secara andal.
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $t) {
            if (!Schema::hasColumn('events', 'sport_category'))    $t->string('sport_category', 100)->nullable();
            if (!Schema::hasColumn('events', 'rundown'))           $t->text('rundown')->nullable();
            if (!Schema::hasColumn('events', 'timezone'))          $t->string('timezone', 10)->nullable()->default('WIB');
            if (!Schema::hasColumn('events', 'payment_category'))  $t->string('payment_category', 20)->nullable();
            if (!Schema::hasColumn('events', 'payment_method'))    $t->string('payment_method', 100)->nullable();
            if (!Schema::hasColumn('events', 'payment_account'))   $t->string('payment_account')->nullable();
            if (!Schema::hasColumn('events', 'organizer_contact')) $t->string('organizer_contact')->nullable();
        });

        // Dipisah supaya draft yang hanya berisi judul bisa disimpan
        Schema::table('events', function (Blueprint $t) {
            $t->string('location')->nullable()->change();
            $t->date('event_date')->nullable()->change();
            $t->time('start_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $t) {
            $cols = array_filter(
                ['sport_category','rundown','timezone','payment_category','payment_method','payment_account','organizer_contact'],
                fn ($c) => Schema::hasColumn('events', $c)
            );
            if ($cols) $t->dropColumn($cols);
        });
    }
};
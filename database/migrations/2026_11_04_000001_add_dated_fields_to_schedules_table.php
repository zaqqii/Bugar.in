<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->date('date')->nullable()->after('community_id');
            $table->string('level')->default('all_levels')->after('title');
            $table->uuid('series_id')->nullable()->index()->after('is_recurring');
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->index(['community_id', 'date']);
            $table->integer('capacity')->nullable()->change();
        });

        // Backfill data lama: isi date dari kemunculan berikutnya day_of_week mulai hari ini (Asia/Jakarta)
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz)->startOfDay();

        $rows = DB::table('schedules')->whereNull('date')->get();
        $dayMap = [
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
        ];

        foreach ($rows as $row) {
            $targetDayStr = strtolower(trim((string) ($row->day_of_week ?? 'monday')));
            $targetDayOfWeek = $dayMap[$targetDayStr] ?? Carbon::MONDAY;
            $daysToAdd = ($targetDayOfWeek - $now->dayOfWeek + 7) % 7;
            $occurrenceDate = $now->copy()->addDays($daysToAdd)->toDateString();

            DB::table('schedules')->where('id', $row->id)->update([
                'date' => $occurrenceDate,
            ]);
        }

        // Setelah backfill, ubah date menjadi NOT NULL
        Schema::table('schedules', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['community_id', 'date']);
            $table->dropIndex(['series_id']);
            $table->dropColumn(['date', 'level', 'series_id', 'cancelled_at']);
        });
    }
};


<?php

namespace App\Http\Controllers\PenyediaKomunitas;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityBlock;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\UserAnnouncement;
use App\Support\NotificationType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalController extends Controller
{
    /**
     * Tampilkan daftar jadwal komunitas penyedia.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->first();

        if (!$community) {
            return view('penyedia-komunitas.jadwal.index', [
                'community' => null,
                'schedules' => collect(),
                'groupedSchedules' => collect(),
                'counts' => [
                    'total' => 0,
                    'upcoming' => 0,
                    'history' => 0,
                    'active' => 0,
                    'inactive' => 0,
                    'total_participants' => 0,
                    'streak_active' => 0,
                ],
                'selectedTab' => 'upcoming',
                'selectedLevel' => 'all',
                'selectedStatus' => 'all',
            ]);
        }

        $allSchedules = $community->schedules()->withCount([
            'participants as registered_participants_count' => function ($q) {
                $q->whereIn('status', ['registered', 'attended']);
            }
        ])->get();

        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);
        $todayStr = $now->toDateString();
        $nowTimeStr = $now->format('H:i:s');

        $upcomingCount = 0;
        $historyCount = 0;
        foreach ($allSchedules as $sch) {
            if ($sch->isFinished()) {
                $historyCount++;
            } else {
                $upcomingCount++;
            }
        }

        $counts = [
            'total' => $allSchedules->count(),
            'upcoming' => $upcomingCount,
            'history' => $historyCount,
            'active' => $allSchedules->where('is_active', true)->count(),
            'inactive' => $allSchedules->where('is_active', false)->count(),
            'total_participants' => $allSchedules->sum('registered_participants_count'),
            'streak_active' => $allSchedules->where('streak_enabled', true)->count(),
        ];

        $selectedTab = $request->query('tab', 'upcoming');
        if (!in_array($selectedTab, ['upcoming', 'history'], true)) {
            $selectedTab = 'upcoming';
        }

        $query = $community->schedules()->withCount([
            'participants as registered_participants_count' => function ($q) {
                $q->whereIn('status', ['registered', 'attended']);
            }
        ]);

        // Filter Tab: Mendatang vs Riwayat
        if ($selectedTab === 'upcoming') {
            $query->where(function ($q) use ($todayStr, $nowTimeStr) {
                $q->whereDate('date', '>', $todayStr)
                  ->orWhere(function ($sub) use ($todayStr, $nowTimeStr) {
                      $sub->whereDate('date', '=', $todayStr)
                          ->where('end_time', '>=', $nowTimeStr);
                  });
            })->orderBy('date', 'asc')->orderBy('start_time', 'asc');
        } else {
            $query->where(function ($q) use ($todayStr, $nowTimeStr) {
                $q->whereDate('date', '<', $todayStr)
                  ->orWhere(function ($sub) use ($todayStr, $nowTimeStr) {
                      $sub->whereDate('date', '=', $todayStr)
                          ->where('end_time', '<', $nowTimeStr);
                  });
            })->orderBy('date', 'desc')->orderBy('start_time', 'desc');
        }

        // Filter Level
        $selectedLevel = $request->query('level', 'all');
        if ($selectedLevel && $selectedLevel !== 'all') {
            $query->where('level', $selectedLevel);
        }

        // Filter Status
        $selectedStatus = $request->query('status', 'all');
        if ($selectedStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($selectedStatus === 'inactive') {
            $query->where('is_active', false);
        } elseif ($selectedStatus === 'cancelled') {
            $query->where(function ($q) {
                $q->whereNotNull('cancelled_at')->orWhere('status', 'cancelled');
            });
        } elseif (in_array($selectedStatus, ['open', 'almost_full', 'full'], true)) {
            $query->where('status', $selectedStatus);
        }

        $filteredSchedules = $query->get();

        // Kelompokkan per tanggal
        $groupedSchedules = $filteredSchedules->groupBy(function (Schedule $item) {
            return $item->date ? $item->date->format('Y-m-d') : 'unspecified';
        });

        return view('penyedia-komunitas.jadwal.index', [
            'community' => $community,
            'schedules' => $filteredSchedules,
            'groupedSchedules' => $groupedSchedules,
            'counts' => $counts,
            'selectedTab' => $selectedTab,
            'selectedLevel' => $selectedLevel,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    /**
     * Form tambah jadwal baru.
     */
    public function create()
    {
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->firstOrFail();

        return view('penyedia-komunitas.jadwal.create', [
            'community' => $community,
            'schedule' => new Schedule(),
        ]);
    }

    /**
     * Simpan jadwal baru ke database.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:80',
            'level' => 'required|string|in:beginner,intermediate,advanced,all_levels',
            'description' => 'nullable|string|max:500',
            'location' => 'required|string|max:120',
            'maps_url' => 'nullable|url|max:255',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity_type' => 'nullable|string|in:unlimited,limited',
            'capacity' => 'nullable|integer|min:1',
            'repeat_weekly_monthly' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'streak_enabled' => 'nullable|boolean',
        ], [
            'title.required' => 'Judul jadwal wajib diisi.',
            'title.max' => 'Judul jadwal maksimal 80 karakter.',
            'level.required' => 'Tingkat keahlian (level) wajib dipilih.',
            'description.max' => 'Deskripsi jadwal maksimal 500 karakter.',
            'location.required' => 'Lokasi jadwal wajib diisi.',
            'location.max' => 'Lokasi jadwal maksimal 120 karakter.',
            'maps_url.url' => 'Tautan Google Maps harus berupa URL valid.',
            'maps_url.max' => 'Tautan Google Maps maksimal 255 karakter.',
            'date.required' => 'Tanggal pelaksanaan wajib diisi.',
            'date.after_or_equal' => 'Tanggal pelaksanaan tidak boleh di masa lalu.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'capacity.min' => 'Kapasitas minimal 1 peserta.',
        ]);

        $capacity = ($request->input('capacity_type') === 'limited')
            ? ($validated['capacity'] ?? null)
            : null;

        $streakEnabled = $request->boolean('streak_enabled');
        if ($streakEnabled && !$community->isVerified()) {
            return back()->withInput()->withErrors([
                'streak_enabled' => 'Komunitas Anda belum terverifikasi. Fitur streak hanya dapat diaktifkan oleh komunitas yang telah diverifikasi.',
            ]);
        }

        $repeatWeeklyMonthly = $request->boolean('repeat_weekly_monthly');
        $tz = config('app.timezone', 'Asia/Jakarta');
        $startDate = Carbon::parse($validated['date'], $tz)->startOfDay();

        $datesToCreate = [];
        $seriesId = $repeatWeeklyMonthly ? (string) Str::uuid() : null;

        if ($repeatWeeklyMonthly) {
            $curr = $startDate->copy();
            $targetMonth = $startDate->month;
            $targetYear = $startDate->year;

            while ($curr->month === $targetMonth && $curr->year === $targetYear) {
                $datesToCreate[] = $curr->toDateString();
                $curr->addWeek();
            }
        } else {
            $datesToCreate[] = $startDate->toDateString();
        }

        DB::transaction(function () use ($datesToCreate, $community, $validated, $capacity, $seriesId, $streakEnabled, $request) {
            foreach ($datesToCreate as $dateString) {
                $schedule = Schedule::create([
                    'community_id' => $community->id,
                    'date' => $dateString,
                    'title' => $validated['title'],
                    'level' => $validated['level'],
                    'description' => $validated['description'] ?? null,
                    'location' => $validated['location'],
                    'maps_url' => $validated['maps_url'] ?? null,
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'capacity' => $capacity,
                    'status' => 'open',
                    'series_id' => $seriesId,
                    'is_recurring' => !empty($seriesId),
                    'is_active' => $request->boolean('is_active', true),
                    'streak_enabled' => $streakEnabled,
                ]);

                $schedule->syncStatus();
            }
        });

        $countCreated = count($datesToCreate);
        $message = $countCreated > 1
            ? "Berhasil membuat {$countCreated} sesi jadwal rutin mingguan untuk bulan ini!"
            : "Jadwal '{$validated['title']}' berhasil dibuat!";

        return redirect()->route('penyedia-komunitas.jadwal.index')
            ->with('success', $message);
    }

    /**
     * Tampilkan detail jadwal dan daftar pesertanya.
     */
    public function show(Schedule $schedule)
    {
        $this->authorizeScheduleOwnership($schedule);

        // Tandai no-show jika jadwal sudah selesai (di belakang konfigurasi flag)
        if ($schedule->isFinished()) {
            $schedule->markNoShows();
        }

        $community = $schedule->community;

        $participants = $schedule->participants()
            ->with('user')
            ->orderBy('registered_at', 'desc')
            ->paginate(20);

        $activeParticipantsCount = $schedule->activeParticipantsCount();

        // Riwayat kehadiran peserta di komunitas ini dihitung dengan SATU query groupBy
        $userIds = $participants->pluck('user_id')->filter()->unique()->values();
        $attendanceStats = collect();

        if ($userIds->isNotEmpty()) {
            $attendanceStats = Participant::query()
                ->join('schedules', 'participants.participatable_id', '=', 'schedules.id')
                ->where('participants.participatable_type', Schedule::class)
                ->where('schedules.community_id', $community->id)
                ->whereIn('participants.user_id', $userIds)
                ->whereIn('participants.status', ['attended', 'no_show'])
                ->groupBy('participants.user_id')
                ->selectRaw('participants.user_id,
                    SUM(CASE WHEN participants.status = ? THEN 1 ELSE 0 END) as attended_count,
                    COUNT(*) as total_evaluated', ['attended'])
                ->get()
                ->keyBy('user_id');
        }

        return view('penyedia-komunitas.jadwal.show', [
            'schedule' => $schedule,
            'community' => $community,
            'participants' => $participants,
            'activeParticipantsCount' => $activeParticipantsCount,
            'attendanceStats' => $attendanceStats,
        ]);
    }

    /**
     * Form edit jadwal.
     */
    public function edit(Schedule $schedule)
    {
        $this->authorizeScheduleOwnership($schedule);

        $community = $schedule->community;

        return view('penyedia-komunitas.jadwal.edit', [
            'schedule' => $schedule,
            'community' => $community,
        ]);
    }

    /**
     * Update data jadwal.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $this->authorizeScheduleOwnership($schedule);

        $community = $schedule->community;
        $activeParticipants = $schedule->activeParticipantsCount();
        $hasActiveParticipants = $activeParticipants > 0;

        $rules = [
            'title' => 'required|string|max:80',
            'level' => 'required|string|in:beginner,intermediate,advanced,all_levels',
            'description' => 'nullable|string|max:500',
            'location' => 'required|string|max:120',
            'maps_url' => 'nullable|url|max:255',
            'capacity_type' => 'nullable|string|in:unlimited,limited',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'streak_enabled' => 'nullable|boolean',
        ];

        // Jika belum ada peserta aktif, tanggal dan jam boleh diubah
        if (!$hasActiveParticipants) {
            $rules['date'] = 'required|date_format:Y-m-d|after_or_equal:today';
            $rules['start_time'] = 'required|date_format:H:i';
            $rules['end_time'] = 'required|date_format:H:i|after:start_time';
        }

        $validated = $request->validate($rules, [
            'title.required' => 'Judul jadwal wajib diisi.',
            'title.max' => 'Judul jadwal maksimal 80 karakter.',
            'level.required' => 'Tingkat keahlian (level) wajib dipilih.',
            'description.max' => 'Deskripsi jadwal maksimal 500 karakter.',
            'location.required' => 'Lokasi jadwal wajib diisi.',
            'location.max' => 'Lokasi jadwal maksimal 120 karakter.',
            'maps_url.url' => 'Tautan Google Maps harus berupa URL valid.',
            'maps_url.max' => 'Tautan Google Maps maksimal 255 karakter.',
            'date.required' => 'Tanggal pelaksanaan wajib diisi.',
            'date.after_or_equal' => 'Tanggal pelaksanaan tidak boleh di masa lalu.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'capacity.min' => 'Kapasitas minimal 1 peserta.',
        ]);

        $capacity = ($request->input('capacity_type') === 'limited')
            ? ($validated['capacity'] ?? null)
            : null;

        if ($capacity !== null && $capacity < $activeParticipants) {
            return back()->withInput()->withErrors([
                'capacity' => "Kapasitas tidak boleh lebih kecil dari jumlah peserta aktif yang sudah terdaftar ({$activeParticipants} peserta).",
            ]);
        }

        $streakEnabled = $request->boolean('streak_enabled');
        if ($streakEnabled && !$community->isVerified()) {
            return back()->withInput()->withErrors([
                'streak_enabled' => 'Komunitas Anda belum terverifikasi. Fitur streak hanya dapat diaktifkan oleh komunitas yang telah diverifikasi.',
            ]);
        }

        $updateData = [
            'title' => $validated['title'],
            'level' => $validated['level'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'],
            'maps_url' => $validated['maps_url'] ?? null,
            'capacity' => $capacity,
            'is_active' => $request->boolean('is_active', true),
            'streak_enabled' => $streakEnabled,
        ];

        if (!$hasActiveParticipants) {
            $updateData['date'] = $validated['date'];
            $updateData['start_time'] = $validated['start_time'];
            $updateData['end_time'] = $validated['end_time'];
        }

        $schedule->update($updateData);
        $schedule->syncStatus();

        return redirect()->route('penyedia-komunitas.jadwal.index')
            ->with('success', 'Jadwal "' . $schedule->title . '" berhasil diperbarui!');
    }

    /**
     * Batalkan satu sesi jadwal.
     */
    public function cancel(Schedule $schedule)
    {
        $this->authorizeScheduleOwnership($schedule);

        if ($schedule->isCancelled()) {
            return back()->with('info', 'Sesi jadwal ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($schedule) {
            $now = now();
            $schedule->update([
                'cancelled_at' => $now,
                'status' => 'cancelled',
            ]);

            $activeParticipants = $schedule->participants()
                ->where('status', 'registered')
                ->get();

            if ($activeParticipants->isNotEmpty()) {
                $schedule->participants()
                    ->where('status', 'registered')
                    ->update([
                        'status' => 'cancelled',
                        'cancelled_at' => $now,
                    ]);

                $dateStr = $schedule->date ? $schedule->date->translatedFormat('l, d F Y') : 'sesi yang dijadwalkan';
                $notifications = [];

                foreach ($activeParticipants as $p) {
                    $notifications[] = [
                        'user_id' => $p->user_id,
                        'type' => NotificationType::SCHEDULE_CANCELLED,
                        'title' => 'Jadwal Dibatalkan: ' . $schedule->title,
                        'body' => 'Pemberitahuan: Sesi latihan "' . $schedule->title . '" (' . $dateStr . ') telah dibatalkan oleh pihak komunitas.',
                        'data' => json_encode([
                            'schedule_id' => $schedule->id,
                            'schedule_title' => $schedule->title,
                            'cancelled_at' => $now->toDateTimeString(),
                        ]),
                        'notifiable_type' => Schedule::class,
                        'notifiable_id' => $schedule->id,
                        'read_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                foreach (array_chunk($notifications, 500) as $chunk) {
                    UserAnnouncement::insert($chunk);
                }
            }
        });

        return redirect()->route('penyedia-komunitas.jadwal.index')
            ->with('success', "Sesi jadwal '{$schedule->title}' berhasil dibatalkan.");
    }

    /**
     * Batalkan seluruh sesi mendatang dalam series yang sama.
     */
    public function cancelSeries(Schedule $schedule)
    {
        $this->authorizeScheduleOwnership($schedule);

        if (empty($schedule->series_id)) {
            return $this->cancel($schedule);
        }

        $now = now();
        $todayStr = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->toDateString();

        $upcomingInSeries = Schedule::where('series_id', $schedule->series_id)
            ->whereDate('date', '>=', $todayStr)
            ->whereNull('cancelled_at')
            ->get();

        if ($upcomingInSeries->isEmpty()) {
            return back()->with('info', 'Tidak ada sesi mendatang dalam seri ini yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($upcomingInSeries, $now) {
            foreach ($upcomingInSeries as $sch) {
                $sch->update([
                    'cancelled_at' => $now,
                    'status' => 'cancelled',
                ]);

                $activeParticipants = $sch->participants()
                    ->where('status', 'registered')
                    ->get();

                if ($activeParticipants->isNotEmpty()) {
                    $sch->participants()
                        ->where('status', 'registered')
                        ->update([
                            'status' => 'cancelled',
                            'cancelled_at' => $now,
                        ]);

                    $dateStr = $sch->date ? $sch->date->translatedFormat('l, d F Y') : 'sesi yang dijadwalkan';
                    $notifications = [];

                    foreach ($activeParticipants as $p) {
                        $notifications[] = [
                            'user_id' => $p->user_id,
                            'type' => NotificationType::SCHEDULE_CANCELLED,
                            'title' => 'Jadwal Seri Dibatalkan: ' . $sch->title,
                            'body' => 'Pemberitahuan: Sesi latihan seri "' . $sch->title . '" (' . $dateStr . ') telah dibatalkan oleh pihak komunitas.',
                            'data' => json_encode([
                                'schedule_id' => $sch->id,
                                'schedule_title' => $sch->title,
                                'cancelled_at' => $now->toDateTimeString(),
                            ]),
                            'notifiable_type' => Schedule::class,
                            'notifiable_id' => $sch->id,
                            'read_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    foreach (array_chunk($notifications, 500) as $chunk) {
                        UserAnnouncement::insert($chunk);
                    }
                }
            }
        });

        $count = $upcomingInSeries->count();
        return redirect()->route('penyedia-komunitas.jadwal.index')
            ->with('success', "Seluruh {$count} sesi mendatang dalam seri jadwal ini berhasil dibatalkan.");
    }

    /**
     * Keluarkan peserta dari jadwal.
     */
    public function removeParticipant(Schedule $schedule, Participant $participant)
    {
        $this->authorizeScheduleOwnership($schedule);

        if ((int) $participant->participatable_id !== (int) $schedule->id || $participant->participatable_type !== Schedule::class) {
            abort(404);
        }

        $participant->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $schedule->syncStatus();

        return back()->with('success', 'Peserta berhasil dikeluarkan dari jadwal.');
    }

    /**
     * Blokir user dari komunitas dan batalkan partisipasi mendatangnya.
     */
    public function blockParticipant(Request $request, Schedule $schedule, Participant $participant)
    {
        $this->authorizeScheduleOwnership($schedule);

        if ((int) $participant->participatable_id !== (int) $schedule->id || $participant->participatable_type !== Schedule::class) {
            abort(404);
        }

        $userId = $participant->user_id;
        $community = $schedule->community;

        DB::transaction(function () use ($community, $userId, $request) {
            CommunityBlock::firstOrCreate([
                'community_id' => $community->id,
                'user_id' => $userId,
            ], [
                'blocked_by' => Auth::id(),
                'reason' => $request->input('reason', 'Diblokir oleh pengelola komunitas.'),
            ]);

            // Batalkan seluruh partisipasi jadwal mendatang user ini di komunitas ini
            $today = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
            $upcomingParticipations = Participant::where('user_id', $userId)
                ->where('participatable_type', Schedule::class)
                ->where('status', 'registered')
                ->whereHasMorph('participatable', [Schedule::class], function ($q) use ($community, $today) {
                    $q->where('community_id', $community->id)
                      ->whereDate('date', '>=', $today)
                      ->whereNull('cancelled_at');
                })
                ->get();

            foreach ($upcomingParticipations as $p) {
                $p->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
                $p->participatable?->syncStatus();
            }
        });

        // Keluarkan juga dari sesi ini jika belum
        if ($participant->status === 'registered') {
            $participant->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            $schedule->syncStatus();
        }

        return back()->with('success', 'Pengguna berhasil diblokir dari komunitas dan seluruh pendaftaran jadwal mendatangnya telah dibatalkan.');
    }

    /**
     * Halaman daftar anggota terblokir pada komunitas.
     */
    public function blocksIndex()
    {
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->firstOrFail();

        $blocks = CommunityBlock::where('community_id', $community->id)
            ->with(['user', 'blocker'])
            ->latest()
            ->paginate(15);

        return view('penyedia-komunitas.jadwal.blokir', [
            'community' => $community,
            'blocks' => $blocks,
        ]);
    }

    /**
     * Buka blokir user dari komunitas.
     */
    public function unblock(CommunityBlock $block)
    {
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->firstOrFail();

        if ((int) $block->community_id !== (int) $community->id) {
            abort(403, 'Akses ditolak.');
        }

        $userName = $block->user?->name ?? 'Pengguna';
        $block->delete();

        return back()->with('success', "Blokir untuk {$userName} berhasil dibuka.");
    }

    /**
     * Toggle status aktif / nonaktif jadwal.
     */
    public function toggleActive(Schedule $schedule)
    {
        $this->authorizeScheduleOwnership($schedule);

        $schedule->is_active = !$schedule->is_active;
        $schedule->save();

        $statusText = $schedule->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('penyedia-komunitas.jadwal.index')
            ->with('success', "Jadwal \"{$schedule->title}\" berhasil {$statusText}.");
    }

    /**
     * Toggle streak enabled per jadwal.
     */
    public function toggleStreak(Schedule $schedule)
    {
        $this->authorizeScheduleOwnership($schedule);

        if (!$schedule->streak_enabled && !$schedule->community->isVerified()) {
            return redirect()->route('penyedia-komunitas.jadwal.index')
                ->with('error', 'Komunitas Anda belum terverifikasi. Verifikasi komunitas terlebih dahulu di menu Akun untuk mengaktifkan fitur streak.');
        }

        $schedule->streak_enabled = !$schedule->streak_enabled;
        $schedule->save();

        $statusText = $schedule->streak_enabled ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('penyedia-komunitas.jadwal.index')
            ->with('success', "Streak untuk jadwal \"{$schedule->title}\" berhasil {$statusText}.");
    }

    /**
     * Hapus jadwal rutin (soft delete).
     */
    public function destroy(Schedule $schedule)
    {
        $this->authorizeScheduleOwnership($schedule);

        $activeParticipants = $schedule->activeParticipantsCount();
        if ($activeParticipants > 0) {
            return redirect()->route('penyedia-komunitas.jadwal.index')
                ->with('error', "Jadwal yang memiliki peserta aktif ({$activeParticipants} peserta) tidak dapat dihapus. Silakan nonaktifkan atau batalkan jadwal ini.");
        }

        $title = $schedule->title;
        $schedule->delete();

        return redirect()->route('penyedia-komunitas.jadwal.index')
            ->with('success', "Jadwal \"{$title}\" berhasil dihapus.");
    }

    /**
     * Pastikan user adalah pemilik komunitas dari jadwal ini.
     */
    protected function authorizeScheduleOwnership(Schedule $schedule): void
    {
        $user = Auth::user();
        if (!$schedule->community || (int) $schedule->community->owner_id !== (int) $user->id) {
            abort(403, 'Anda tidak memiliki hak akses ke jadwal ini.');
        }
    }
}

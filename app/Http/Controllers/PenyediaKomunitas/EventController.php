<?php

namespace App\Http\Controllers\PenyediaKomunitas;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use App\Models\UserAnnouncement;
use App\Support\NotificationType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->first();

        if (!$community) {
            return view('penyedia-komunitas.events.index', [
                'community' => null,
                'events' => collect(),
                'counts' => [
                    'total' => 0,
                    'published' => 0,
                    'registration_closed' => 0,
                    'ongoing' => 0,
                    'completed' => 0,
                    'draft' => 0,
                    'cancelled' => 0,
                    'total_participants' => 0,
                ],
                'selectedStatus' => 'all',
                'search' => '',
            ]);
        }

        $allEvents = $community->events()->get();
        $counts = [
            'total' => $allEvents->count(),
            'published' => $allEvents->where('status', 'published')->count(),
            'registration_closed' => $allEvents->where('status', 'registration_closed')->count(),
            'ongoing' => $allEvents->where('status', 'ongoing')->count(),
            'completed' => $allEvents->where('status', 'completed')->count(),
            'draft' => $allEvents->where('status', 'draft')->count(),
            'cancelled' => $allEvents->where('status', 'cancelled')->count(),
            'total_participants' => $allEvents->sum('participants_count'),
        ];

        $query = $community->events();

        $selectedStatus = $request->query('status', 'all');
        if ($selectedStatus && $selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        $search = trim($request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(location) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        $events = $query->orderBy('event_date', 'desc')
                        ->orderBy('start_time', 'desc')
                        ->paginate(10)
                        ->withQueryString();

        return view('penyedia-komunitas.events.index', compact(
            'community', 'events', 'counts', 'selectedStatus', 'search'
        ));
    }

    public function create()
    {
        $community = Community::where('owner_id', Auth::id())->first();
        return view('penyedia-komunitas.events.create_event', compact('community'));
    }

    public function store(Request $request)
    {
        @ini_set('max_execution_time', '180');
        @set_time_limit(180);

        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:80',
            'type' => 'required|string|in:fun_match,tournament,gathering,workshop,other',
            'description' => 'required|string',
            'rundown' => 'nullable|string',
            'cover_image' => $cover . '|image|mimes:jpeg,png,jpg,webp|max:2048',
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'event_date' => $r . '|date',
            'registration_close_at' => $r . '|date',
            'timezone' => 'nullable|in:WIB,WITA,WIT',
            'start_time' => $r,
            'end_time' => 'nullable',
            'location' => $r . '|string|max:255',
            'maps_url' => 'nullable|url|max:500',
            'capacity_type' => 'nullable|in:limited,unlimited',
            'capacity' => 'nullable|integer|min:1',
            'is_paid' => 'nullable|in:0,1',
            'registration_fee' => 'nullable|numeric|min:0',
            'payment_category' => 'nullable|in:bank,qris,ewallet,cash',
            'payment_method' => 'nullable|string|max:100',
            'payment_account' => 'nullable|string|max:255',
            'requirements' => 'nullable|string',
            'organizer_contact' => 'nullable|string|max:255',
            'action' => 'nullable|string|in:draft,publish',
            'status' => 'nullable|string|in:draft,published',
        ], [
            'title.max' => 'Nama event tidak boleh lebih dari 80 karakter agar tampilan tetap rapi.',
        ]);

        $isDraft = ($request->input('action') === 'draft') || ($request->input('status') === 'draft');
        $initialStatus = $isDraft ? 'draft' : 'published';

        // Verifikasi Komunitas: Hanya jika mempublikasikan event berbayar
        $isPaidEvent = ($request->is_paid === '1') || ((float) ($validated['registration_fee'] ?? 0) > 0);
        if (!$isDraft && $isPaidEvent && !$community->isVerified()) {
            return back()->withInput()->withErrors([
                'is_paid' => 'Komunitas Anda belum terverifikasi. Hanya komunitas terverifikasi yang dapat membuat event berbayar (tiket). Komunitas yang belum terverifikasi hanya dapat membuat jadwal dan event gratis, atau simpan sebagai Draft terlebih dahulu.',
            ]);
        }

        // Upload Cover Image
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $disk = config('filesystems.default');
            try {
                $coverPath = $request->file('cover_image')->store('events/covers', $disk);
            } catch (\Throwable $e) {
                Log::error('Gagal mengunggah cover event ke storage: ' . $e->getMessage());
                return back()->withInput()->withErrors([
                    'cover_image' => 'Gagal mengunggah gambar sampul karena masalah jaringan ke cloud storage. Silakan coba lagi atau gunakan gambar yang lebih ringkas.',
                ]);
            }
        }

        // Format Description (sertakan Rundown jika ada)
        $description = $validated['description'];
        if (!empty($validated['rundown'])) {
            $description .= "\n\n--- RUNDOWN ACARA ---\n" . $validated['rundown'];
        }

        // Format Requirements (sertakan info pembayaran dan kontak jika ada)
        $requirements = $validated['requirements'] ?? '';
        $extraInfo = [];
        if (!empty($validated['timezone'])) {
            $extraInfo[] = "Zona Waktu Acara: " . $validated['timezone'];
        }
        if (!empty($validated['payment_method'])) {
            $extraInfo[] = "Metode Pembayaran: " . $validated['payment_method'];
        }
        if (!empty($validated['payment_account'])) {
            $extraInfo[] = "No. Rekening / Tujuan: " . $validated['payment_account'];
        }
        if (!empty($validated['organizer_contact'])) {
            $extraInfo[] = "Kontak Penyelenggara: " . $validated['organizer_contact'];
        }

        if (!empty($extraInfo)) {
            $requirements = trim($requirements . "\n\n--- INFORMASI PENDAFTARAN & KONTAK ---\n" . implode("\n", $extraInfo));
        }

        // Kapasitas dan Biaya
        $capacity = ($request->capacity_type === 'unlimited') ? null : ($validated['capacity'] ?? null);
        $registrationFee = ($request->is_paid === '1') ? ($validated['registration_fee'] ?? 0) : 0;

        // Generate Unique Slug
        $slug = Str::slug($validated['title']) . '-' . Str::lower(Str::random(6));

        // Create Event
        Event::create([
            'community_id' => $community->id,
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $description,
            'cover_image' => $coverPath,
            'type' => $validated['type'],
            'location' => $validated['location'],
            'maps_url' => $validated['maps_url'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'capacity' => $capacity,
            'registration_fee' => $registrationFee,
            'requirements' => $requirements ?: null,
            'registration_open_at' => $isDraft ? null : now(),
            'registration_close_at' => $validated['registration_close_at'],
            'status' => $initialStatus,
            'participants_count' => 0,
            'is_active' => true,
        ]);

        $message = $isDraft
            ? 'Draft event "' . $validated['title'] . '" berhasil disimpan!'
            : 'Event "' . $validated['title'] . '" berhasil dipublikasikan!';

        return redirect()->route('penyedia-komunitas.events.index')->with('success', $message);
    }

    public function edit(Event $event)
    {
        $this->authorizeEventOwnership($event);

        if (!$event->canBeEdited()) {
            return redirect()->route('penyedia-komunitas.events.index')
                ->with('error', 'Event yang sudah selesai atau dibatalkan tidak dapat diedit.');
        }

        $community = $event->community;

        // Parse extracted rundown and extra info for edit form
        $parsed = $this->parseEventFields($event);

        return view('penyedia-komunitas.events.create_event', compact('community', 'event', 'parsed'));
    }

    public function update(Request $request, Event $event)
    {
        @ini_set('max_execution_time', '180');
        @set_time_limit(180);

        $this->authorizeEventOwnership($event);

        if (!$event->canBeEdited()) {
            return redirect()->route('penyedia-komunitas.events.index')
                ->with('error', 'Event yang sudah selesai atau dibatalkan tidak dapat diedit.');
        }

        $community = $event->community;

        $validated = $request->validate([
            'title' => 'required|string|max:80',
            'type' => 'required|string|in:fun_match,tournament,gathering,workshop,other',
            'description' => 'required|string',
            'rundown' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'event_date' => 'required|date',
            'registration_close_at' => 'required|date',
            'timezone' => 'nullable|string|in:WIB,WITA,WIT',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'location' => 'required|string|max:255',
            'maps_url' => 'nullable|url|max:500',
            'capacity_type' => 'nullable|string|in:limited,unlimited',
            'capacity' => 'nullable|integer|min:1',
            'is_paid' => 'nullable|string|in:0,1',
            'registration_fee' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'payment_account' => 'nullable|string|max:255',
            'requirements' => 'nullable|string',
            'organizer_contact' => 'nullable|string|max:255',
            'action' => 'nullable|string|in:draft,publish',
            'status' => 'nullable|string|in:draft,published',
        ], [
            'title.max' => 'Nama event tidak boleh lebih dari 80 karakter agar tampilan tetap rapi.',
        ]);

        $isDraftAction = ($request->input('action') === 'draft') || ($request->input('status') === 'draft');

        // Status logic on update:
        // Event yang sudah terbit / bukan draft tidak boleh dikembalikan ke status draft
        if ($event->status === 'draft') {
            $newStatus = $isDraftAction ? 'draft' : 'published';
        } else {
            $newStatus = $event->status;
        }

        // Pengecekan komunitas verified jika event berbayar dan akan dipublikasikan / sedang terbit
        $isPaidEvent = ($request->is_paid === '1') || ((float) ($validated['registration_fee'] ?? 0) > 0);
        if ($newStatus !== 'draft' && $isPaidEvent && !$community->isVerified()) {
            return back()->withInput()->withErrors([
                'is_paid' => 'Komunitas Anda belum terverifikasi. Hanya komunitas terverifikasi yang dapat membuat event berbayar (tiket).',
            ]);
        }

        // Kapasitas tidak boleh di bawah jumlah peserta aktif
        $capacity = ($request->capacity_type === 'unlimited') ? null : ($validated['capacity'] ?? null);
        $activeParticipants = $event->activeParticipantsCount();
        if ($capacity !== null && $capacity < $activeParticipants) {
            return back()->withInput()->withErrors([
                'capacity' => "Kapasitas tidak boleh lebih kecil dari jumlah peserta aktif yang sudah terdaftar ({$activeParticipants} peserta).",
            ]);
        }

        // Handle Cover Image: jika ganti, hapus file lama dari disk
        $coverPath = $event->cover_image;
        if ($request->hasFile('cover_image')) {
            $disk = config('filesystems.default');
            try {
                if ($event->cover_image && Storage::disk($disk)->exists($event->cover_image)) {
                    Storage::disk($disk)->delete($event->cover_image);
                }
                $coverPath = $request->file('cover_image')->store('events/covers', $disk);
            } catch (\Throwable $e) {
                Log::error('Gagal mengunggah cover event baru ke storage: ' . $e->getMessage());
                return back()->withInput()->withErrors([
                    'cover_image' => 'Gagal mengunggah gambar sampul baru karena kendala jaringan storage. Silakan coba lagi.',
                ]);
            }
        }

        // Format Description (sertakan Rundown jika ada)
        $description = $validated['description'];
        if (!empty($validated['rundown'])) {
            $description .= "\n\n--- RUNDOWN ACARA ---\n" . $validated['rundown'];
        }

        // Format Requirements
        $requirements = $validated['requirements'] ?? '';
        $extraInfo = [];
        if (!empty($validated['timezone'])) {
            $extraInfo[] = "Zona Waktu Acara: " . $validated['timezone'];
        }
        if (!empty($validated['payment_method'])) {
            $extraInfo[] = "Metode Pembayaran: " . $validated['payment_method'];
        }
        if (!empty($validated['payment_account'])) {
            $extraInfo[] = "No. Rekening / Tujuan: " . $validated['payment_account'];
        }
        if (!empty($validated['organizer_contact'])) {
            $extraInfo[] = "Kontak Penyelenggara: " . $validated['organizer_contact'];
        }

        if (!empty($extraInfo)) {
            $requirements = trim($requirements . "\n\n--- INFORMASI PENDAFTARAN & KONTAK ---\n" . implode("\n", $extraInfo));
        }

        $registrationFee = ($request->is_paid === '1') ? ($validated['registration_fee'] ?? 0) : 0;

        $updateData = [
            'title' => $validated['title'],
            'description' => $description,
            'cover_image' => $coverPath,
            'type' => $validated['type'],
            'location' => $validated['location'],
            'maps_url' => $validated['maps_url'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'capacity' => $capacity,
            'registration_fee' => $registrationFee,
            'requirements' => $requirements ?: null,
            'registration_close_at' => $validated['registration_close_at'],
            'status' => $newStatus,
        ];

        // Jika transisi dari draft ke published, set registration_open_at jika belum ada
        if ($newStatus === 'published' && !$event->registration_open_at) {
            $updateData['registration_open_at'] = now();
        }

        $event->update($updateData);

        return redirect()->route('penyedia-komunitas.events.index')
            ->with('success', 'Perubahan event "' . $validated['title'] . '" berhasil disimpan!');
    }

    public function publish(Event $event)
    {
        $this->authorizeEventOwnership($event);

        if (!$event->canBePublished()) {
            return redirect()->route('penyedia-komunitas.events.index')
                ->with('error', 'Hanya event dengan status Draft yang dapat dipublikasikan.');
        }

        if ($event->registration_fee > 0 && !$event->community->isVerified()) {
            return redirect()->route('penyedia-komunitas.events.index')
                ->with('error', 'Komunitas Anda belum terverifikasi. Hanya komunitas terverifikasi yang dapat mempublikasikan event berbayar.');
        }

        $event->update([
            'status' => 'published',
            'registration_open_at' => $event->registration_open_at ?? now(),
        ]);

        return redirect()->route('penyedia-komunitas.events.index')
            ->with('success', 'Event "' . $event->title . '" berhasil dipublikasikan!');
    }

    public function closeRegistration(Event $event)
    {
        $this->authorizeEventOwnership($event);

        if (!$event->canCloseRegistration()) {
            return redirect()->route('penyedia-komunitas.events.index')
                ->with('error', 'Pendaftaran hanya dapat ditutup untuk event yang sedang berstatus Pendaftaran Dibuka.');
        }

        $event->update([
            'status' => 'registration_closed',
            'registration_close_at' => now(),
        ]);

        return redirect()->route('penyedia-komunitas.events.index')
            ->with('success', 'Pendaftaran untuk event "' . $event->title . '" berhasil ditutup.');
    }

    public function cancel(Event $event)
    {
        $this->authorizeEventOwnership($event);

        if (!$event->canBeCancelled()) {
            return redirect()->route('penyedia-komunitas.events.index')
                ->with('error', 'Event ini tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($event) {
            $event->update(['status' => 'cancelled']);

            // Peserta aktif yang perlu diberi tahu
            $participants = $event->participants()
                ->whereIn('status', ['registered', 'waiting_list', 'waiting_payment'])
                ->get();

            $notifications = [];
            $now = now();
            $dateFormatted = $event->event_date ? $event->event_date->translatedFormat('d F Y') : 'yang dijadwalkan';

            foreach ($participants as $p) {
                $notifications[] = [
                    'user_id' => $p->user_id,
                    'type' => NotificationType::EVENT_CANCELLED,
                    'title' => 'Event Dibatalkan: ' . $event->title,
                    'body' => 'Pemberitahuan: Event "' . $event->title . '" (' . $dateFormatted . ') telah dibatalkan oleh penyelenggara komunitas.',
                    'data' => json_encode([
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'cancelled_at' => $now->toDateTimeString(),
                    ]),
                    'notifiable_type' => Event::class,
                    'notifiable_id' => $event->id,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Insert massal per 500 baris
            foreach (array_chunk($notifications, 500) as $chunk) {
                UserAnnouncement::insert($chunk);
            }
        });

        return redirect()->route('penyedia-komunitas.events.index')
            ->with('success', 'Event "' . $event->title . '" berhasil dibatalkan dan notifikasi telah dikirim ke seluruh peserta.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeEventOwnership($event);

        if (!$event->canBeDeleted()) {
            return redirect()->route('penyedia-komunitas.events.index')
                ->with('error', 'Event yang sudah memiliki peserta tidak dapat dihapus. Silakan gunakan fitur Batalkan Event.');
        }

        $title = $event->title;
        $event->delete();

        return redirect()->route('penyedia-komunitas.events.index')
            ->with('success', 'Event "' . $title . '" berhasil dihapus.');
    }

    protected function authorizeEventOwnership(Event $event): void
    {
        $user = Auth::user();
        if (!$event->community || (int) $event->community->owner_id !== (int) $user->id) {
            abort(403, 'Anda tidak memiliki hak akses ke event ini.');
        }
    }

    protected function parseEventFields(Event $event): array
    {
        $description = $event->description ?? '';
        $rundown = '';
        if (str_contains($description, "\n\n--- RUNDOWN ACARA ---\n")) {
            $parts = explode("\n\n--- RUNDOWN ACARA ---\n", $description, 2);
            $description = $parts[0];
            $rundown = $parts[1] ?? '';
        }

        $requirements = $event->requirements ?? '';
        $timezone = 'WIB';
        $paymentMethod = '';
        $paymentAccount = '';
        $organizerContact = '';

        if (str_contains($requirements, "\n\n--- INFORMASI PENDAFTARAN & KONTAK ---\n")) {
            $parts = explode("\n\n--- INFORMASI PENDAFTARAN & KONTAK ---\n", $requirements, 2);
            $requirements = $parts[0];
            $extra = $parts[1] ?? '';

            foreach (explode("\n", $extra) as $line) {
                $line = trim($line);
                if (str_starts_with($line, 'Zona Waktu Acara: ')) {
                    $timezone = trim(str_replace('Zona Waktu Acara: ', '', $line));
                } elseif (str_starts_with($line, 'Metode Pembayaran: ')) {
                    $paymentMethod = trim(str_replace('Metode Pembayaran: ', '', $line));
                } elseif (str_starts_with($line, 'No. Rekening / Tujuan: ')) {
                    $paymentAccount = trim(str_replace('No. Rekening / Tujuan: ', '', $line));
                } elseif (str_starts_with($line, 'Kontak Penyelenggara: ')) {
                    $organizerContact = trim(str_replace('Kontak Penyelenggara: ', '', $line));
                }
            }
        }

        return [
            'description' => $description,
            'rundown' => $rundown,
            'requirements' => $requirements,
            'timezone' => $timezone,
            'payment_method' => $paymentMethod,
            'payment_account' => $paymentAccount,
            'organizer_contact' => $organizerContact,
        ];
    }

    private function extraErrors(Request $r, ?Event $event): array
    {
        $e = [];

        if ($r->event_date && $r->registration_close_at
            && Carbon::parse($r->registration_close_at)->toDateString() > Carbon::parse($r->event_date)->toDateString()) {
            $e['registration_close_at'] = 'Batas pendaftaran tidak boleh melewati tanggal acara.';
        }

        if ($r->capacity_type !== 'unlimited' && !$r->capacity) {
            $e['capacity'] = 'Kuota peserta wajib diisi minimal 1.';
        }

        if ($r->is_paid === '1') {
            if ((float) $r->registration_fee <= 0) {
                $e['registration_fee'] = 'Nominal biaya pendaftaran wajib diisi.';
            }
            $cat = $r->payment_category;
            if ($cat === 'qris') {
                if (!$r->hasFile('qris_image') && !$event?->qris_image) {
                    $e['qris_image'] = 'Foto barcode QRIS wajib diunggah.';
                }
            } elseif (in_array($cat, ['bank', 'ewallet']) && !filled($r->payment_account)) {
                $e['payment_account'] = 'Nomor rekening / tujuan pembayaran wajib diisi.';
            }
        }

        return $e;
    }

    private function payload(Request $r, array $v): array
    {
        $paid = ($v['is_paid'] ?? '0') === '1';
        $cat = $paid ? ($v['payment_category'] ?? null) : null;
        $type = $v['type'] ?? 'other';

        return [
            'title' => $v['title'],
            'description' => $v['description'] ?? null,
            'rundown' => $v['rundown'] ?? null,
            'type' => $type === 'conference' ? 'workshop' : $type,
            'sport_category' => $v['sport_category'] ?? null,
            'location' => $v['location'] ?? null,
            'maps_url' => $v['maps_url'] ?? null,
            'event_date' => $v['event_date'] ?? null,
            'start_time' => $v['start_time'] ?? null,
            'end_time' => $v['end_time'] ?? null,
            'timezone' => $v['timezone'] ?? 'WIB',
            'registration_close_at' => $v['registration_close_at'] ?? null,
            'capacity' => $r->capacity_type === 'unlimited' ? null : ($v['capacity'] ?? null),
            'registration_fee' => $paid ? ($v['registration_fee'] ?? 0) : 0,
            'payment_category' => $cat,
            'payment_method' => $cat ? ($v['payment_method'] ?? null) : null,
            'payment_account' => in_array($cat, ['bank', 'ewallet']) ? ($v['payment_account'] ?? null) : null,
            'requirements' => $v['requirements'] ?? null,
            'organizer_contact' => $v['organizer_contact'] ?? null,
        ];
    }

    private function handleFiles(Request $r, ?Event $event, array &$data): void
    {
        $disk = config('filesystems.default');

        if ($r->hasFile('cover_image')) {
            if ($event?->cover_image) {
                Storage::disk($disk)->delete($event->cover_image);
            }
            $data['cover_image'] = $r->file('cover_image')->store('events/covers', $disk);
        }

        $usesQris = ($data['payment_category'] ?? null) === 'qris';
        if ($usesQris && $r->hasFile('qris_image')) {
            if ($event?->qris_image) {
                Storage::disk($disk)->delete($event->qris_image);
            }
            $data['qris_image'] = $r->file('qris_image')->store('events/qris', $disk);
        } elseif (!$usesQris && $event?->qris_image) {
            Storage::disk($disk)->delete($event->qris_image);
            $data['qris_image'] = null;
        }
    }

    public function show(Event $event)
    {
        $event = $this->own($event);

        return view(
            'penyedia-komunitas.events.show_event',
            compact('event')
        );
    }

    public function manage(Event $event)
    {
        $event = $this->own($event);

        return view(
            'penyedia-komunitas.events.show_event',
            compact('event')
        );
    }
}
<?php

namespace App\Http\Controllers\PenyediaKomunitas;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
                    'total' => 0, 'published' => 0, 'ongoing' => 0,
                    'completed' => 0, 'draft' => 0, 'total_participants' => 0,
                ],
                'selectedStatus' => 'all',
                'search' => '',
            ]);
        }

        $allEvents = $community->events()->get();
        $counts = [
            'total' => $allEvents->count(),
            'published' => $allEvents->where('status', 'published')->count(),
            'ongoing' => $allEvents->where('status', 'ongoing')->count(),
            'completed' => $allEvents->where('status', 'completed')->count(),
            'draft' => $allEvents->where('status', 'draft')->count(),
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
        $community = $this->community();
        $draft = $request->input('status') === 'draft';

        $v = $request->validate($this->rules($draft));
        if (!$draft && ($err = $this->extraErrors($request, null))) {
            return back()->withErrors($err)->withInput();
        }

        $data = $this->payload($request, $v);
        $this->handleFiles($request, null, $data);

        Event::create(array_merge($data, [
            'community_id' => $community->id,
            'slug' => Str::slug($v['title']) . '-' . Str::lower(Str::random(6)),
            'status' => $draft ? 'draft' : 'published',
            'registration_open_at' => $draft ? null : now(),
            'participants_count' => 0,
            'is_active' => true,
        ]));

        if ($draft) {
            return redirect()->route('penyedia-komunitas.events.index', ['status' => 'draft'])
                ->with('success', 'Draft "' . $v['title'] . '" berhasil disimpan.');
        }

        return redirect()->route('penyedia-komunitas.dashboard')
            ->with('success', 'Event "' . $v['title'] . '" berhasil dipublikasikan!');
    }

    public function edit(Event $event)
    {
        $community = $this->own($event);
        return view('penyedia-komunitas.events.create_event', compact('community', 'event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->own($event);
        // event yang sudah terbit tidak boleh kembali ke draft
        $draft = $request->input('status') === 'draft' && $event->status === 'draft';

        $v = $request->validate($this->rules($draft, $event));
        if (!$draft && ($err = $this->extraErrors($request, $event))) {
            return back()->withErrors($err)->withInput();
        }

        $data = $this->payload($request, $v);
        $this->handleFiles($request, $event, $data);

        if ($draft) {
            $data['status'] = 'draft';
        } else {
            $data['status'] = $event->status === 'draft' ? 'published' : $event->status;
            if (!$event->registration_open_at) {
                $data['registration_open_at'] = now();
            }
        }

        $event->update($data);

        return redirect()->route('penyedia-komunitas.events.index', ['status' => $draft ? 'draft' : 'all'])
            ->with('success', $draft ? 'Draft berhasil diperbarui.' : 'Event "' . $event->title . '" berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        $this->own($event);

        if ($event->status !== 'draft' && $event->participants_count > 0) {
            return back()->with('error', 'Event sudah memiliki peserta dan tidak dapat dihapus.');
        }

        $title = $event->title;
        $event->delete(); // soft delete, file gambar dibiarkan

        return redirect()->route('penyedia-komunitas.events.index')
            ->with('success', 'Event "' . $title . '" berhasil dihapus.');
    }

    /* ---------------- Helpers ---------------- */

    private function community(): Community
    {
        return Community::where('owner_id', Auth::id())->firstOrFail();
    }

    private function own(Event $event): Community
    {
        $c = $this->community();
        abort_unless($event->community_id === $c->id, 403);
        return $c;
    }

    private function rules(bool $draft, ?Event $event = null): array
    {
        $r = $draft ? 'nullable' : 'required';
        $cover = ($draft || $event?->cover_image) ? 'nullable' : 'required';

        return [
            'title' => 'required|string|max:255',
            'type' => $r . '|string|in:fun_match,tournament,gathering,conference,workshop,other',
            'sport_category' => $r . '|string|max:100',
            'description' => $r . '|string',
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
            'organizer_contact' => $r . '|string|max:255',
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
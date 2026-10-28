<?php

namespace App\Http\Controllers\PenyediaKomunitas;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                    'ongoing' => 0,
                    'completed' => 0,
                    'draft' => 0,
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
            'ongoing' => $allEvents->where('status', 'ongoing')->count(),
            'completed' => $allEvents->where('status', 'completed')->count(),
            'draft' => $allEvents->where('status', 'draft')->count(),
            'total_participants' => $allEvents->sum('participants_count'),
        ];

        $query = $community->events();

        // Filter status
        $selectedStatus = $request->query('status', 'all');
        if ($selectedStatus && $selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        // Search title & location
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
            'community',
            'events',
            'counts',
            'selectedStatus',
            'search'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->first();

        return view('penyedia-komunitas.events.create_event', compact('community'));
    }

    public function store(Request $request)
{
    $community = Community::where('owner_id', Auth::id())->firstOrFail();

    $isDraft = $request->input('status') === 'draft';
    $req = $isDraft ? 'nullable' : 'required';

    $v = $request->validate([
        'title' => 'required|string|max:255',
        'type' => "$req|string|in:fun_match,tournament,gathering,conference,workshop,other",
        'sport_category' => "$req|string|max:100",
        'description' => "$req|string",
        'rundown' => 'nullable|string',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'qris_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'event_date' => "$req|date",
        'registration_close_at' => "$req|date",
        'timezone' => 'nullable|string|in:WIB,WITA,WIT',
        'start_time' => $req,
        'end_time' => 'nullable',
        'location' => "$req|string|max:255",
        'maps_url' => 'nullable|url|max:500',
        'capacity_type' => 'nullable|string|in:limited,unlimited',
        'capacity' => 'nullable|integer|min:1',
        'is_paid' => 'nullable|string|in:0,1',
        'registration_fee' => 'nullable|numeric|min:0',
        'payment_category' => 'nullable|string|in:bank,qris,ewallet,cash',
        'payment_method' => 'nullable|string|max:100',
        'payment_account' => 'nullable|string|max:255',
        'requirements' => 'nullable|string',
        'organizer_contact' => "$req|string|max:255",
    ]);

    // Validasi khusus berbayar (hanya saat publish)
    if (!$isDraft && $request->is_paid === '1') {
        if ($request->payment_category === 'qris') {
            if (!$request->hasFile('qris_image')) {
                return back()->withErrors(['qris_image' => 'Foto barcode QRIS wajib diunggah.'])->withInput();
            }
        } elseif (in_array($request->payment_category, ['bank', 'ewallet']) && empty($v['payment_account'])) {
            return back()->withErrors(['payment_account' => 'Nomor rekening / tujuan pembayaran wajib diisi.'])->withInput();
        }
    }

    // Upload gambar
    $disk = config('filesystems.default');
    $coverPath = $request->hasFile('cover_image') ? $request->file('cover_image')->store('events/covers', $disk) : null;
    $qrisPath = $request->hasFile('qris_image') ? $request->file('qris_image')->store('events/qris', $disk) : null;

    // Deskripsi + rundown
    $description = $v['description'] ?? '';
    if (!empty($v['rundown'])) {
        $description .= "\n\n--- RUNDOWN ACARA ---\n" . $v['rundown'];
    }

    // Syarat + info pembayaran & kontak
    $extra = array_filter([
        !empty($v['timezone']) ? 'Zona Waktu Acara: ' . $v['timezone'] : null,
        !empty($v['payment_method']) ? 'Metode Pembayaran: ' . $v['payment_method'] : null,
        ($v['payment_method'] ?? '') === 'QRIS'
            ? 'Metode: Scan Barcode QRIS'
            : (!empty($v['payment_account']) ? 'No. Rekening / Tujuan: ' . $v['payment_account'] : null),
        !empty($v['organizer_contact']) ? 'Kontak Penyelenggara: ' . $v['organizer_contact'] : null,
    ]);
    $requirements = $v['requirements'] ?? '';
    if ($extra) {
        $requirements = trim($requirements . "\n\n--- INFORMASI PENDAFTARAN & KONTAK ---\n" . implode("\n", $extra));
    }

    $type = $v['type'] ?? 'fun_match';

    Event::create([
        'community_id' => $community->id,
        'title' => $v['title'],
        'slug' => Str::slug($v['title']) . '-' . Str::lower(Str::random(6)),
        'description' => $description ?: null,
        'cover_image' => $coverPath,
        'qris_image' => $qrisPath,
        'type' => $type === 'conference' ? 'workshop' : $type,
        'location' => $v['location'] ?? null,
        'maps_url' => $v['maps_url'] ?? null,
        'event_date' => $v['event_date'] ?? null,
        'start_time' => $v['start_time'] ?? null,
        'end_time' => $v['end_time'] ?? null,
        'capacity' => $request->capacity_type === 'unlimited' ? null : ($v['capacity'] ?? null),
        'registration_fee' => $request->is_paid === '1' ? ($v['registration_fee'] ?? 0) : 0,
        'requirements' => $requirements ?: null,
        'registration_open_at' => $isDraft ? null : now(),
        'registration_close_at' => $v['registration_close_at'] ?? null,
        'status' => $isDraft ? 'draft' : 'published',
        'participants_count' => 0,
        'is_active' => !$isDraft,
    ]);

    return $isDraft
        ? redirect()->route('penyedia-komunitas.events.index', ['status' => 'draft'])
            ->with('success', 'Draft "' . $v['title'] . '" berhasil disimpan.')
        : redirect()->route('penyedia-komunitas.dashboard')
            ->with('success', 'Event "' . $v['title'] . '" berhasil dipublikasikan!');
}
}

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
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:fun_match,tournament,gathering,conference,workshop,other',
            'sport_category' => 'required|string|max:100',
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
        ]);

        // Verifikasi Komunitas: Hanya komunitas terverifikasi yang boleh membuat event berbayar
        $isPaidEvent = ($request->is_paid === '1') || ((float) ($validated['registration_fee'] ?? 0) > 0);
        if ($isPaidEvent && !$community->isVerified()) {
            return back()->withInput()->withErrors([
                'is_paid' => 'Komunitas Anda belum terverifikasi. Hanya komunitas terverifikasi yang dapat membuat event berbayar (tiket). Komunitas yang belum terverifikasi hanya dapat membuat jadwal dan event gratis.',
            ]);
        }

        // Upload Cover Image
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $disk = config('filesystems.default');
            $coverPath = $request->file('cover_image')->store('events/covers', $disk);
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
            'type' => ($validated['type'] === 'conference') ? 'workshop' : $validated['type'],
            'location' => $validated['location'],
            'maps_url' => $validated['maps_url'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'capacity' => $capacity,
            'registration_fee' => $registrationFee,
            'requirements' => $requirements ?: null,
            'registration_open_at' => now(),
            'registration_close_at' => $validated['registration_close_at'],
            'status' => 'published',
            'participants_count' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('penyedia-komunitas.dashboard')->with('success', 'Event "' . $validated['title'] . '" berhasil dipublikasikan!');
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Report;
use App\Models\User;
use App\Models\UserAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Kategori alasan pelaporan yang valid (sentence case)
     */
    public const COMMUNITY_REASONS = [
        'Pungutan liar & penipuan jadwal',
        'Konten / informasi komunitas palsu',
        'Pelanggaran aturan komunitas & norma sosial',
        'Pelecehan / diskriminasi SARA',
        'Spam / promosi judi / iklan terlarang',
        'Lainnya',
    ];

    public const USER_REASONS = [
        'Perilaku kasar & pelecehan verbal',
        'Ujaran kebencian & SARA',
        'Spam / promosi judi / iklan terlarang',
        'Pelecehan seksual / perilaku tidak pantas',
        'Akun palsu / penipuan identitas',
        'Lainnya',
    ];

    /**
     * Tampilkan riwayat laporan saya (sisi user)
     */
    public function index()
    {
        $reports = Report::where('reporter_id', Auth::id())
            ->with(['reportable'])
            ->latest()
            ->paginate(10);

        return view('user.reports.index', compact('reports'));
    }

    /**
     * Tampilkan form pelaporan untuk Komunitas
     */
    public function createCommunity($communityId)
    {
        $query = Community::where('is_active', true);

        if (is_numeric($communityId)) {
            $query->where(function ($q) use ($communityId) {
                $q->where('id', (int) $communityId)
                  ->orWhere('slug', (string) $communityId);
            });
        } else {
            $query->where('slug', (string) $communityId);
        }

        $community = $query->firstOrFail();

        // Cek apakah pelapor adalah pemilik komunitas itu sendiri (anti self-report)
        if ($community->owner_id === Auth::id()) {
            return redirect()->route('user.communities.show', $community->slug ?? $community->id)
                ->with('error', 'Anda tidak dapat melaporkan komunitas milik Anda sendiri.');
        }

        // Cek satu laporan terbuka per pasangan pelapor-terlapor (pending / under_review)
        $existingReport = Report::where('reporter_id', Auth::id())
            ->where('reportable_type', Community::class)
            ->where('reportable_id', $community->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->first();

        $reasons = self::COMMUNITY_REASONS;

        return view('user.reports.create-community', compact('community', 'reasons', 'existingReport'));
    }

    /**
     * Simpan laporan Komunitas
     */
    public function storeCommunity(Request $request, $communityId)
    {
        $query = Community::query();

        if (is_numeric($communityId)) {
            $query->where(function ($q) use ($communityId) {
                $q->where('id', (int) $communityId)
                  ->orWhere('slug', (string) $communityId);
            });
        } else {
            $query->where('slug', (string) $communityId);
        }

        $community = $query->firstOrFail();

        // 1. Anti self-report
        if ($community->owner_id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat melaporkan komunitas milik Anda sendiri.');
        }

        // 2. Satu laporan terbuka per pasangan pelapor-terlapor
        $hasOpenReport = Report::where('reporter_id', Auth::id())
            ->where('reportable_type', Community::class)
            ->where('reportable_id', $community->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        if ($hasOpenReport) {
            return back()->with('error', 'Anda masih memiliki laporan aktif terhadap komunitas ini yang sedang diproses admin.');
        }

        // 3. Validasi
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:15', 'max:1500'],
            'evidence_url' => ['nullable', 'url', 'max:500'],
            'evidence_images' => ['nullable', 'array', 'max:5'],
            'evidence_images.*' => ['file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'reason.required' => 'Pilih kategori alasan pelaporan.',
            'description.required' => 'Deskripsi kronologi laporan wajib diisi.',
            'description.min' => 'Deskripsi minimal 15 karakter agar admin dapat memverifikasi.',
            'evidence_url.url' => 'Tautan bukti pendukung harus berupa URL yang valid (contoh: https://drive.google.com/...).',
            'evidence_images.max' => 'Maksimal 5 foto bukti yang dapat diunggah.',
            'evidence_images.*.mimes' => 'Format foto bukti harus berupa jpeg, png, jpg, atau webp.',
            'evidence_images.*.max' => 'Ukuran setiap foto bukti maksimal 2MB.',
        ]);

        // Upload bukti foto ke storage privat (r2_private jika terkonfigurasi, fallback ke local private)
        $uploadedImages = [];
        if ($request->hasFile('evidence_images')) {
            $disk = config('filesystems.disks.r2_private') ? 'r2_private' : 'local';
            foreach ($request->file('evidence_images') as $file) {
                $path = $file->store('reports/evidence', $disk);
                if ($path) {
                    $uploadedImages[] = $path;
                }
            }
        }

        // 4. Simpan laporan dengan assignment eksplisit
        $report = new Report();
        $report->reporter_id = Auth::id();
        $report->reportable_type = Community::class;
        $report->reportable_id = $community->id;
        $report->community_id = $community->id; // Butir 1: community_id diturunkan dari komunitas
        $report->reason = $request->input('reason');
        $report->description = $request->input('description');
        $report->evidence = $request->input('evidence_url'); // Bukti berupa tautan/teks
        if (!empty($uploadedImages)) {
            $report->evidence_images = $uploadedImages; // Bukti foto di bucket privat
        }
        $report->status = 'pending';
        $report->save();

        return redirect()->route('user.reports.index')
            ->with('success', "Laporan terhadap komunitas '{$community->name}' berhasil dikirim. Tim admin akan meninjaunya.");
    }

    /**
     * Tampilkan form pelaporan untuk User lain (misal sesama peserta kegiatan)
     */
    public function createUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // 1. Anti self-report
        if ($user->id === Auth::id()) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Anda tidak dapat melaporkan akun Anda sendiri.');
        }

        // 2. Validasi konteks komunitas jika ada
        $community = null;
        $communityId = $request->query('community_id');
        if ($communityId) {
            $community = Community::where(function ($q) use ($communityId) {
                if (is_numeric($communityId)) {
                    $q->where('id', (int) $communityId)->orWhere('slug', (string) $communityId);
                } else {
                    $q->where('slug', (string) $communityId);
                }
            })->first();

            // Cek apakah user yang dilaporkan terafiliasi dengan komunitas ini
            if ($community) {
                $isAffiliated = $community->owner_id === $user->id
                    || $community->members()->where('user_id', $user->id)->exists()
                    || \App\Models\Participant::where('user_id', $user->id)
                        ->where(function ($q) use ($community) {
                            $q->whereHasMorph('participatable', [\App\Models\Schedule::class, \App\Models\Event::class], function ($query) use ($community) {
                                $query->where('community_id', $community->id);
                            });
                        })->exists();

                if (!$isAffiliated) {
                    $community = null; // Tidak valid sebagai konteks komunitas terlapor
                }
            }
        }

        // 3. Cek apakah ada laporan terbuka
        $existingReport = Report::where('reporter_id', Auth::id())
            ->where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->first();

        $reasons = self::USER_REASONS;

        return view('user.reports.create-user', compact('user', 'reasons', 'existingReport', 'community'));
    }

    /**
     * Simpan laporan User lain
     */
    public function storeUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // 1. Anti self-report
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat melaporkan akun Anda sendiri.');
        }

        // 2. Satu laporan terbuka per pasangan pelapor-terlapor
        $hasOpenReport = Report::where('reporter_id', Auth::id())
            ->where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        if ($hasOpenReport) {
            return back()->with('error', 'Anda masih memiliki laporan aktif terhadap pengguna ini yang sedang diproses admin.');
        }

        // 3. Validasi komunitas konteks: pastikan user terlapor memang anggota/peserta/owner komunitas itu
        $validatedCommunityId = null;
        if ($request->filled('community_id')) {
            $inputCommId = $request->input('community_id');
            $comm = Community::where(function ($q) use ($inputCommId) {
                if (is_numeric($inputCommId)) {
                    $q->where('id', (int) $inputCommId)->orWhere('slug', (string) $inputCommId);
                } else {
                    $q->where('slug', (string) $inputCommId);
                }
            })->first();

            if (!$comm) {
                return back()->with('error', 'Komunitas konteks laporan tidak ditemukan.');
            }

            // Validasi di backend bahwa user terlapor memang terafiliasi dengan komunitas ini
            $isAffiliated = $comm->owner_id === $user->id
                || $comm->members()->where('user_id', $user->id)->exists()
                || \App\Models\Participant::where('user_id', $user->id)
                    ->where(function ($q) use ($comm) {
                        $q->whereHasMorph('participatable', [\App\Models\Schedule::class, \App\Models\Event::class], function ($query) use ($comm) {
                            $query->where('community_id', $comm->id);
                        });
                    })->exists();

            if (!$isAffiliated) {
                return back()->with('error', 'Pengguna yang dilaporkan bukan anggota atau peserta pada komunitas yang dipilih.');
            }

            $validatedCommunityId = $comm->id;
        }

        // 4. Validasi input
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:15', 'max:1500'],
            'evidence_url' => ['nullable', 'url', 'max:500'],
            'evidence_images' => ['nullable', 'array', 'max:5'],
            'evidence_images.*' => ['file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'reason.required' => 'Pilih kategori alasan pelaporan.',
            'description.required' => 'Deskripsi kronologi laporan wajib diisi.',
            'description.min' => 'Deskripsi minimal 15 karakter agar admin dapat memverifikasi.',
            'evidence_url.url' => 'Tautan bukti pendukung harus berupa URL yang valid.',
            'evidence_images.max' => 'Maksimal 5 foto bukti yang dapat diunggah.',
            'evidence_images.*.mimes' => 'Format foto bukti harus berupa jpeg, png, jpg, atau webp.',
            'evidence_images.*.max' => 'Ukuran setiap foto bukti maksimal 2MB.',
        ]);

        // Upload bukti foto ke storage privat
        $uploadedImages = [];
        if ($request->hasFile('evidence_images')) {
            $disk = config('filesystems.disks.r2_private') ? 'r2_private' : 'local';
            foreach ($request->file('evidence_images') as $file) {
                $path = $file->store('reports/evidence', $disk);
                if ($path) {
                    $uploadedImages[] = $path;
                }
            }
        }

        // 5. Simpan laporan dengan assignment eksplisit
        $report = new Report();
        $report->reporter_id = Auth::id();
        $report->reportable_type = User::class;
        $report->reportable_id = $user->id;
        $report->community_id = $validatedCommunityId; // Butir 1: community_id tervalidasi
        $report->reason = $request->input('reason');
        $report->description = $request->input('description');
        $report->evidence = $request->input('evidence_url'); // Bukti tautan
        if (!empty($uploadedImages)) {
            $report->evidence_images = $uploadedImages; // Bukti foto di bucket privat
        }
        $report->status = 'pending';
        $report->save();

        // Kirimkan notifikasi ke pengelola komunitas bahwa ada aduan anggota di komunitasnya
        if ($comm && $comm->owner_id) {
            UserAnnouncement::create([
                'user_id' => $comm->owner_id,
                'type' => 'announcement',
                'title' => 'Aduan anggota: ' . $comm->name,
                'body' => "Terdapat aduan pelanggaran terhadap anggota '{$user->name}' di komunitas Anda (alasan: {$report->reason}). Silakan tinjau dan teruskan ke admin platform jika memerlukan mitigasi penonaktifan akun.",
                'data' => [
                    'report_id' => $report->id,
                    'community_id' => $comm->id,
                    'reported_user_id' => $user->id,
                ],
                'notifiable_type' => Community::class,
                'notifiable_id' => $comm->id,
            ]);
        }

        return redirect()->route('user.reports.index')
            ->with('success', "Laporan terhadap pengguna '{$user->name}' berhasil dikirim ke pengelola komunitas dan tim admin.");
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Kategori alasan pelaporan yang valid
     */
    public const COMMUNITY_REASONS = [
        'Pungutan Liar & Penipuan Jadwal',
        'Konten / Informasi Komunitas Palsu',
        'Pelanggaran Aturan Komunitas & Norma Sosial',
        'Pelecehan / Diskriminasi SARA',
        'Spam / Promosi Judi / Iklan Terlarang',
        'Lainnya',
    ];

    public const USER_REASONS = [
        'Perilaku Kasar & Pelecehan Verbal',
        'Ujaran Kebencian & SARA',
        'Spam / Promosi Judi / Iklan Terlarang',
        'Pelecehan Seksual / Perilaku Tidak Pantas',
        'Akun Palsu / Penipuan Identitas',
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
        ], [
            'reason.required' => 'Pilih kategori alasan pelaporan.',
            'description.required' => 'Deskripsi kronologi laporan wajib diisi.',
            'description.min' => 'Deskripsi minimal 15 karakter agar admin dapat memverifikasi.',
            'evidence_url.url' => 'Tautan bukti pendukung harus berupa URL yang valid (contoh: https://drive.google.com/...).',
        ]);

        // 4. Simpan laporan
        $report = new Report();
        $report->reporter_id = Auth::id();
        $report->reportable_type = Community::class;
        $report->reportable_id = $community->id;
        $report->reason = $request->input('reason');
        $report->description = $request->input('description');
        $report->evidence = $request->input('evidence_url'); // Sesuai aturan MVP: bukti berupa tautan/teks
        $report->status = 'pending';
        $report->save();

        return redirect()->route('user.reports.index')
            ->with('success', "Laporan terhadap komunitas '{$community->name}' berhasil dikirim. Tim admin akan meninjaunya.");
    }

    /**
     * Tampilkan form pelaporan untuk User lain (misal sesama peserta kegiatan)
     */
    public function createUser($userId)
    {
        $user = User::findOrFail($userId);

        // 1. Anti self-report
        if ($user->id === Auth::id()) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Anda tidak dapat melaporkan akun Anda sendiri.');
        }

        // 2. Cek apakah ada laporan terbuka
        $existingReport = Report::where('reporter_id', Auth::id())
            ->where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->first();

        $reasons = self::USER_REASONS;

        return view('user.reports.create-user', compact('user', 'reasons', 'existingReport'));
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

        // 3. Validasi
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:15', 'max:1500'],
            'evidence_url' => ['nullable', 'url', 'max:500'],
        ], [
            'reason.required' => 'Pilih kategori alasan pelaporan.',
            'description.required' => 'Deskripsi kronologi laporan wajib diisi.',
            'description.min' => 'Deskripsi minimal 15 karakter agar admin dapat memverifikasi.',
            'evidence_url.url' => 'Tautan bukti pendukung harus berupa URL yang valid.',
        ]);

        // 4. Simpan laporan
        $report = new Report();
        $report->reporter_id = Auth::id();
        $report->reportable_type = User::class;
        $report->reportable_id = $user->id;
        $report->reason = $request->input('reason');
        $report->description = $request->input('description');
        $report->evidence = $request->input('evidence_url');
        $report->status = 'pending';
        $report->save();

        return redirect()->route('user.reports.index')
            ->with('success', "Laporan terhadap pengguna '{$user->name}' berhasil dikirim. Tim admin akan meninjaunya.");
    }
}

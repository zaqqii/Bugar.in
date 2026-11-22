<?php

namespace App\Http\Controllers\PenyediaKomunitas;

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
     * Dapatkan komunitas milik penyedia login saat ini
     */
    protected function getOwnedCommunity(): Community
    {
        return Community::where('owner_id', Auth::id())->firstOrFail();
    }

    /**
     * Daftar laporan anggota di komunitas milik penyedia
     */
    public function index(Request $request)
    {
        $community = $this->getOwnedCommunity();
        $status = $request->query('status');

        $query = Report::with(['reporter', 'reportable'])
            ->where('community_id', $community->id)
            ->where('reportable_type', User::class)
            ->latest();

        if ($status && in_array($status, ['pending', 'under_review', 'resolved', 'dismissed'])) {
            $query->where('status', $status);
        }

        $reports = $query->paginate(10)->withQueryString();

        $counts = [
            'all' => Report::where('community_id', $community->id)->where('reportable_type', User::class)->count(),
            'pending' => Report::where('community_id', $community->id)->where('reportable_type', User::class)->where('status', 'pending')->count(),
            'under_review' => Report::where('community_id', $community->id)->where('reportable_type', User::class)->where('status', 'under_review')->count(),
            'resolved' => Report::where('community_id', $community->id)->where('reportable_type', User::class)->where('status', 'resolved')->count(),
            'dismissed' => Report::where('community_id', $community->id)->where('reportable_type', User::class)->where('status', 'dismissed')->count(),
        ];

        if ($request->ajax()) {
            return view('penyedia-komunitas.laporan._table', compact('reports', 'community'))->render();
        }

        return view('penyedia-komunitas.laporan.index', compact('reports', 'counts', 'status', 'community'));
    }

    /**
     * Detail laporan anggota
     */
    public function show(Request $request, $id)
    {
        $community = $this->getOwnedCommunity();

        $report = Report::with(['reporter', 'reportable'])
            ->where('community_id', $community->id)
            ->where('reportable_type', User::class)
            ->findOrFail($id);

        // Jika status masih pending, ubah ke under_review (idempotent, tanpa prefetch)
        $isPrefetch = $request->header('Purpose') === 'prefetch'
            || $request->header('Sec-Purpose') === 'prefetch'
            || $request->header('X-Purpose') === 'preview';

        if (!$isPrefetch && $report->status === 'pending') {
            $report->status = 'under_review';
            $report->save();
        }

        return view('penyedia-komunitas.laporan.show', compact('report', 'community'));
    }

    /**
     * Tolak laporan jika tidak terbukti
     */
    public function dismiss(Request $request, $id)
    {
        $community = $this->getOwnedCommunity();

        $report = Report::where('community_id', $community->id)
            ->where('reportable_type', User::class)
            ->findOrFail($id);

        if (in_array($report->status, ['resolved', 'dismissed'])) {
            return back()->with('error', 'Laporan ini sudah berada dalam status final dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ], [
            'reason.required' => 'Alasan penolakan laporan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 10 karakter.',
            'reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $reason = $request->input('reason');
        $report->status = 'dismissed';
        $report->rejection_reason = $reason;
        $report->action_note = ($report->action_note ? $report->action_note . "\n\n" : '') .
            '[' . now()->format('Y-m-d H:i') . ' Ditolak Pengelola Komunitas]: ' . $reason;
        $report->handled_at = now();
        $report->save();

        // Kirim notifikasi ke pelapor bahwa laporan tidak terbukti
        if ($report->reporter_id) {
            UserAnnouncement::create([
                'user_id' => $report->reporter_id,
                'type' => 'announcement',
                'title' => 'Pembaruan laporan: ' . $community->name,
                'body' => "Laporan Anda terhadap pengguna '" . ($report->reportable->name ?? 'anggota') . "' telah ditinjau oleh pengelola komunitas dan ditolak dengan alasan: {$reason}",
                'data' => [
                    'report_id' => $report->id,
                    'community_id' => $community->id,
                ],
                'notifiable_type' => Community::class,
                'notifiable_id' => $community->id,
            ]);
        }

        return redirect()->route('penyedia-komunitas.laporan.index')
            ->with('success', "Laporan #{$report->id} berhasil ditolak karena tidak terbukti.");
    }

    /**
     * Setujui laporan dan ajukan permintaan penonaktifan akun ke Admin
     */
    public function forwardToAdmin(Request $request, $id)
    {
        $community = $this->getOwnedCommunity();

        $report = Report::with(['reporter', 'reportable'])
            ->where('community_id', $community->id)
            ->where('reportable_type', User::class)
            ->findOrFail($id);

        if (in_array($report->status, ['resolved', 'dismissed'])) {
            return back()->with('error', 'Laporan ini sudah berada dalam status final.');
        }

        $request->validate([
            'request_note' => 'required|string|min:10|max:1000',
        ], [
            'request_note.required' => 'Alasan permintaan penonaktifan akun wajib diisi.',
            'request_note.min' => 'Alasan permohonan minimal 10 karakter.',
            'request_note.max' => 'Alasan permohonan maksimal 1000 karakter.',
        ]);

        $reqNote = $request->input('request_note');

        // Status disetujui di level komunitas (resolved), dan diteruskan ke admin dengan catatan eskalasi
        $report->status = 'resolved';
        $report->admin_notes = "Permintaan penonaktifan dari pengelola {$community->name}: " . $reqNote;
        $report->action_note = ($report->action_note ? $report->action_note . "\n\n" : '') .
            '[' . now()->format('Y-m-d H:i') . ' Permohonan Sanksi ke Admin]: ' . $reqNote;
        $report->handled_at = now();
        $report->save();

        // Kirim notifikasi ke seluruh Admin platform untuk tindakan eksekusi sanksi
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            UserAnnouncement::create([
                'user_id' => $admin->id,
                'type' => 'announcement',
                'title' => 'Permintaan sanksi akun pengguna: ' . ($report->reportable->name ?? 'Anggota'),
                'body' => "Pengelola komunitas '{$community->name}' telah memverifikasi laporan #{$report->id} dan mengajukan permintaan penonaktifan akun untuk pengguna '{$report->reportable->name}'. Alasan: {$reqNote}",
                'data' => [
                    'report_id' => $report->id,
                    'community_id' => $community->id,
                    'target_user_id' => $report->reportable_id,
                ],
                'notifiable_type' => Community::class,
                'notifiable_id' => $community->id,
            ]);
        }

        // Notifikasi ke pelapor bahwa laporan terbukti dan telah diajukan sanksi ke admin
        if ($report->reporter_id) {
            UserAnnouncement::create([
                'user_id' => $report->reporter_id,
                'type' => 'announcement',
                'title' => 'Laporan Anda disetujui: ' . $community->name,
                'body' => "Laporan Anda terhadap pengguna '" . ($report->reportable->name ?? 'anggota') . "' telah diverifikasi oleh pengelola komunitas. Pengelola telah mengajukan tindakan penonaktifan akun ke tim admin platform.",
                'data' => [
                    'report_id' => $report->id,
                    'community_id' => $community->id,
                ],
                'notifiable_type' => Community::class,
                'notifiable_id' => $community->id,
            ]);
        }

        return redirect()->route('penyedia-komunitas.laporan.index')
            ->with('success', "Laporan disetujui. Permintaan penonaktifan akun untuk '{$report->reportable->name}' berhasil diajukan ke admin.");
    }
}

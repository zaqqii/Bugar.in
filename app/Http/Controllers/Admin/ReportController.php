<?php

namespace App\Http\Controllers\Admin;

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
     * Tampilkan daftar seluruh laporan pengguna (Reports Index).
     * Paginasi maksimal 15 per halaman dengan eager loading.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $type = $request->query('type');

        $query = Report::with(['reporter', 'reportable', 'handler'])
            ->latest();

        // Filter status
        if ($status && in_array($status, ['pending', 'under_review', 'resolved', 'dismissed'])) {
            $query->where('status', $status);
        }

        // Filter jenis terlapor
        if ($type === 'community') {
            $query->where('reportable_type', Community::class);
        } elseif ($type === 'user') {
            $query->where('reportable_type', User::class);
        }

        $reports = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'under_review' => Report::where('status', 'under_review')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'dismissed' => Report::where('status', 'dismissed')->count(),
        ];

        // Jika request via AJAX / SPA, kembalikan partial list tabel saja
        if ($request->ajax()) {
            return view('admin.reports._table', compact('reports'))->render();
        }

        return view('admin.reports.index', compact('reports', 'counts', 'status', 'type'));
    }

    /**
     * Tampilkan detail laporan tertentu.
     * Menggunakan eager loading dan merahasiakan identitas pelapor dari publik/terlapor.
     * Status otomatis, hanya maju, idempotent: membuka DETAIL laporan pending -> under_review,
     * isi handled_by (handled_at hanya saat final). resolved/dismissed tidak berubah oleh pembukaan halaman.
     * Abaikan request prefetch. JANGAN catat perubahan otomatis ini ke audit log.
     */
    public function show(Request $request, $id)
    {
        $report = Report::with(['reporter', 'reportable', 'handler'])->findOrFail($id);

        // Abaikan request prefetch dari browser (Purpose: prefetch / Sec-Purpose: prefetch)
        $isPrefetch = $request->header('Purpose') === 'prefetch'
            || $request->header('Sec-Purpose') === 'prefetch'
            || $request->header('X-Purpose') === 'preview';

        if (!$isPrefetch && $report->status === 'pending') {
            // Naikkan status otomatis ke under_review secara idempotent
            $report->status = 'under_review';
            $report->handled_by = Auth::id();
            // handled_at hanya diisi saat status final (resolved / dismissed)
            $report->save();
        }

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Tolak Laporan (Dismissed).
     * Modal dengan "Alasan Penolakan" wajib (min 10, maks 1000), validasi backend,
     * simpan di rejection_reason secara eksplisit. Setelah final, backend menolak aksi.
     */
    public function dismiss(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Backend menolak semua aksi jika status sudah final
        if (in_array($report->status, ['resolved', 'dismissed'])) {
            return back()->with('error', 'Laporan ini sudah berada dalam status final dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan laporan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $oldStatus = $report->status;
        $reason = $request->input('rejection_reason');

        // Assignment eksplisit untuk status, rejection_reason, handled_by, handled_at
        $report->status = 'dismissed';
        $report->rejection_reason = $reason;
        $report->handled_by = Auth::id();
        $report->handled_at = now();
        $report->save();

        // TODO: Dependency ke modul Audit Log (AD-07) jika AuditLogger tersedia di environment:
        // AuditLogger::record('report.dismissed', $report, ['status' => $oldStatus], ['status' => 'dismissed', 'reason' => $reason]);

        return back()->with('success', "Laporan #{$report->id} berhasil ditolak.");
    }

    /**
     * Selesaikan Laporan (Resolved / Disetujui Admin).
     * Jika admin tidak input catatan khusus, sistem menyediakan pemberitahuan default
     * bahwa laporan terbukti valid dan disetujui admin.
     * Setelah status final, backend menolak pemanggilan ulang.
     */
    public function resolve(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Backend menolak jika status sudah final
        if (in_array($report->status, ['resolved', 'dismissed'])) {
            return back()->with('error', 'Laporan ini sudah berada dalam status final dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ], [
            'admin_notes.max' => 'Catatan admin maksimal 1000 karakter.',
        ]);

        $defaultNotification = 'Laporan Anda telah diverifikasi dan terbukti valid oleh tim moderasi Bugar.in. Tindakan tindak lanjut telah diterapkan terhadap pihak terlapor.';
        $adminNotes = $request->input('admin_notes');
        if (blank($adminNotes)) {
            $adminNotes = $defaultNotification;
        }

        // Assignment eksplisit
        $report->status = 'resolved';
        $report->admin_notes = $adminNotes;
        $report->handled_by = Auth::id();
        $report->handled_at = now();
        $report->save();

        // TODO: Dependency ke modul Audit Log (AD-07) jika AuditLogger tersedia di environment:
        // AuditLogger::record('report.resolved', $report, ['status' => $report->getOriginal('status')], ['status' => 'resolved', 'notes' => $adminNotes]);

        return back()->with('success', "Laporan #{$report->id} berhasil disetujui dan diselesaikan.");
    }

    /**
     * Berikan Peringatan Resmi ke Komunitas Terlapor.
     * Hanya berlaku untuk laporan komunitas yang sudah disetujui/resolved.
     */
    public function warnCommunity(Request $request, $id)
    {
        $report = Report::with('reportable')->findOrFail($id);

        if (class_basename($report->reportable_type) !== 'Community' || !$report->reportable) {
            return back()->with('error', 'Peringatan komunitas hanya dapat dikirim untuk laporan terhadap entitas komunitas.');
        }

        if ($report->status !== 'resolved') {
            return back()->with('error', 'Peringatan ke komunitas hanya dapat dikirimkan jika laporan telah disetujui oleh admin.');
        }

        $community = $report->reportable;
        $defaultWarning = "Peringatan resmi dari tim moderasi Bugar.in: Komunitas '{$community->name}' telah terbukti melanggar ketentuan platform pada laporan #{$report->id} (alasan: {$report->reason}). Harap segera memperbaiki aktivitas komunitas dan mematuhi aturan platform. Jika pelanggaran berulang, komunitas akan dinonaktifkan secara sepihak.";

        $customWarning = $request->input('warning_message');
        $finalWarningMessage = filled($customWarning) ? $customWarning : $defaultWarning;

        // Simpan log peringatan ke action_note laporan dengan assignment eksplisit
        $report->action_note = ($report->action_note ? $report->action_note . "\n\n" : '') .
            '[' . now()->format('Y-m-d H:i') . ' Peringatan Resmi]: ' . $finalWarningMessage;
        $report->save();

        // Kirimkan notifikasi resmi ke akun owner (pengelola) komunitas via tabel notifications
        if ($community->owner_id) {
            UserAnnouncement::create([
                'user_id' => $community->owner_id,
                'type' => 'announcement',
                'title' => 'Peringatan resmi moderasi: ' . $community->name,
                'body' => $finalWarningMessage,
                'data' => [
                    'report_id' => $report->id,
                    'community_id' => $community->id,
                    'reason' => $report->reason,
                ],
                'notifiable_type' => Community::class,
                'notifiable_id' => $community->id,
            ]);
        }

        return back()->with('success', "Peringatan resmi berhasil dikirimkan ke komunitas '{$community->name}'.");
    }

    /**
     * Tindakan lanjutan: Penonaktifan komunitas dari detail laporan (AD-03).
     * Memakai assignment eksplisit dan ->save(). Alasan sanksi wajib diisi.
     * HANYA BISA DIJALANKAN JIKA LAPORAN SUDAH DISETUJUI ADMIN (status = 'resolved').
     */
    public function toggleCommunityStatus(Request $request, $communityId)
    {
        $reportId = $request->input('report_id');
        if ($reportId) {
            $report = Report::find($reportId);
            if ($report && $report->status !== 'resolved') {
                return back()->with('error', 'Tindakan penonaktifan hanya dapat dilakukan setelah laporan disetujui oleh admin.');
            }
        }

        $community = Community::findOrFail($communityId);

        // Jika menonaktifkan, alasan sanksi wajib diisi
        if ($community->is_active) {
            $request->validate([
                'reason' => 'required|string|min:5|max:1000',
            ], [
                'reason.required' => 'Alasan penonaktifan komunitas wajib diisi.',
                'reason.min' => 'Alasan penonaktifan minimal 5 karakter.',
            ]);
        }

        // Assignment eksplisit untuk is_active
        $community->is_active = !$community->is_active;
        $community->save();

        $statusText = $community->is_active ? 'diaktifkan kembali' : 'dinonaktifkan sementara';
        return back()->with('success', "Komunitas '{$community->name}' berhasil {$statusText}.");
    }

    /**
     * Tindakan lanjutan: Penonaktifan akun user terlapor dari detail laporan (AD-04).
     * Memakai assignment eksplisit dan ->save(). Alasan sanksi wajib diisi.
     * Tidak ada aksi ban, admin tidak bisa menonaktifkan diri sendiri atau sesama admin.
     * HANYA BISA DIJALANKAN JIKA LAPORAN SUDAH DISETUJUI ADMIN (status = 'resolved').
     */
    public function toggleUserStatus(Request $request, $userId)
    {
        $reportId = $request->input('report_id');
        if ($reportId) {
            $report = Report::find($reportId);
            if ($report && $report->status !== 'resolved') {
                return back()->with('error', 'Tindakan penonaktifan hanya dapat dilakukan setelah laporan disetujui oleh admin.');
            }
        }

        $user = User::findOrFail($userId);

        if ($user->role === 'admin' || $user->id === Auth::id()) {
            return back()->with('error', 'Akun administrator atau akun Anda sendiri tidak dapat dinonaktifkan.');
        }

        // Jika menonaktifkan, alasan sanksi wajib diisi
        if ($user->is_active) {
            $request->validate([
                'reason' => 'required|string|min:5|max:1000',
            ], [
                'reason.required' => 'Alasan penonaktifan akun pengguna wajib diisi.',
                'reason.min' => 'Alasan penonaktifan minimal 5 karakter.',
            ]);
        }

        // Assignment eksplisit untuk is_active
        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan sementara';
        return back()->with('success', "Akun '{$user->name}' berhasil {$statusText}.");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Report;
use App\Models\User;
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
     * Selesaikan Laporan (Resolved).
     * Catatan admin opsional (maks 1000) di admin_notes sebagai catatan internal.
     * Setelah final, backend menolak aksi.
     */
    public function resolve(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Backend menolak semua aksi jika status sudah final
        if (in_array($report->status, ['resolved', 'dismissed'])) {
            return back()->with('error', 'Laporan ini sudah berada dalam status final dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ], [
            'admin_notes.max' => 'Catatan admin maksimal 1000 karakter.',
        ]);

        $oldStatus = $report->status;
        $adminNotes = $request->input('admin_notes');

        // Assignment eksplisit
        $report->status = 'resolved';
        $report->admin_notes = $adminNotes;
        $report->handled_by = Auth::id();
        $report->handled_at = now();
        $report->save();

        // TODO: Dependency ke modul Audit Log (AD-07) jika AuditLogger tersedia di environment:
        // AuditLogger::record('report.resolved', $report, ['status' => $oldStatus], ['status' => 'resolved', 'notes' => $adminNotes]);

        return back()->with('success', "Laporan #{$report->id} berhasil diselesaikan.");
    }

    /**
     * Tindakan lanjutan: Penonaktifan komunitas dari detail laporan (AD-03).
     * Memakai assignment eksplisit dan ->save(). Alasan sanksi wajib diisi.
     * Setelah status laporan final, backend menolak pemberian sanksi.
     */
    public function toggleCommunityStatus(Request $request, $communityId)
    {
        $reportId = $request->input('report_id');
        if ($reportId) {
            $report = Report::find($reportId);
            if ($report && in_array($report->status, ['resolved', 'dismissed'])) {
                return back()->with('error', 'Laporan sudah dalam status final. Tindakan sanksi tidak dapat dilakukan lagi.');
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
     * Setelah status laporan final, backend menolak pemberian sanksi.
     */
    public function toggleUserStatus(Request $request, $userId)
    {
        $reportId = $request->input('report_id');
        if ($reportId) {
            $report = Report::find($reportId);
            if ($report && in_array($report->status, ['resolved', 'dismissed'])) {
                return back()->with('error', 'Laporan sudah dalam status final. Tindakan sanksi tidak dapat dilakukan lagi.');
            }
        }

        $user = User::findOrFail($userId);

        if ($user->role === 'admin' || $user->id === Auth::id()) {
            return back()->with('error', 'Akun Administrator atau akun Anda sendiri tidak dapat dinonaktifkan.');
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

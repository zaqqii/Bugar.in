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
     */
    public function show($id)
    {
        $report = Report::with(['reporter', 'reportable', 'handler'])->findOrFail($id);

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Perbarui status penanganan laporan oleh Admin.
     * Memakai assignment eksplisit dan ->save() sesuai aturan Sub-bab 2.3.
     */
    public function updateStatus(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,under_review,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ], [
            'status.required' => 'Pilih status penanganan laporan.',
            'status.in' => 'Status laporan tidak valid.',
            'admin_notes.max' => 'Catatan admin maksimal 1000 karakter.',
        ]);

        $report->status = $request->input('status');
        $report->admin_notes = $request->input('admin_notes');
        $report->handled_by = Auth::id();
        $report->handled_at = now();
        $report->save();

        return back()->with('success', "Status laporan #{$report->id} berhasil diperbarui menjadi {$report->status}.");
    }

    /**
     * Tindakan lanjutan: Penonaktifan komunitas dari detail laporan (AD-03).
     * Memakai assignment eksplisit dan ->save().
     */
    public function toggleCommunityStatus(Request $request, $communityId)
    {
        $community = Community::findOrFail($communityId);

        // Assignment eksplisit untuk is_active
        $community->is_active = !$community->is_active;
        $community->save();

        $statusText = $community->is_active ? 'diaktifkan kembali' : 'dinonaktifkan sementara';
        return back()->with('success', "Komunitas '{$community->name}' berhasil {$statusText}.");
    }

    /**
     * Tindakan lanjutan: Penonaktifan akun user terlapor dari detail laporan (AD-04).
     * Memakai assignment eksplisit dan ->save().
     */
    public function toggleUserStatus(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->role === 'admin') {
            return back()->with('error', 'Akun Administrator tidak dapat dinonaktifkan.');
        }

        // Assignment eksplisit untuk is_active
        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan sementara';
        return back()->with('success', "Akun '{$user->name}' berhasil {$statusText}.");
    }
}

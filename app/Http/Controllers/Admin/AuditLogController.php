<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Community;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Halaman index daftar lengkap audit log (read-only).
     */
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'auditable'])->latest();

        // Filter Aksi
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter Target Entitas
        if ($request->filled('target')) {
            if ($request->target === 'community') {
                $query->where('auditable_type', Community::class);
            } elseif ($request->target === 'user') {
                $query->where('auditable_type', User::class);
            } elseif ($request->target === 'report') {
                $query->where('auditable_type', Report::class);
            }
        }

        // Filter Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'actions'));
    }

    /**
     * Endpoint API / AJAX untuk widget recent activity di Dashboard.
     * Mengembalikan view partial _list atau JSON.
     */
    public function recentActivity(Request $request)
    {
        $query = AuditLog::with(['user', 'auditable'])->latest();

        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', 'like', $request->action . '%');
        }

        $auditLogs = $query->paginate(6);

        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.audit-logs._list', compact('auditLogs'))->render();
        }

        return response()->json($auditLogs);
    }
}

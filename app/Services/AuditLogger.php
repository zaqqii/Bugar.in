<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Catat aksi audit ke dalam database (audit_logs).
     *
     * @param string $action Nama aksi, contoh: 'community.verified', 'user.suspended', 'report.resolved'
     * @param Model $subject Model objek yang dikenai perubahan (Community, User, Report, dll)
     * @param array|null $old Nilai sebelum aksi (opsional)
     * @param array|null $new Nilai sesudah aksi (opsional)
     * @return AuditLog|null
     */
    public static function record(string $action, Model $subject, ?array $old = null, ?array $new = null): ?AuditLog
    {
        try {
            $log = new AuditLog();
            $log->user_id = Auth::id();
            $log->action = $action;
            $log->auditable_type = get_class($subject);
            $log->auditable_id = $subject->getKey();
            $log->old_values = $old;
            $log->new_values = $new;
            $log->ip_address = Request::ip();
            $log->user_agent = substr((string) Request::header('User-Agent'), 0, 255);
            $log->save();

            return $log;
        } catch (\Throwable $e) {
            // Jangan biarkan kegagalan logging menjatuhkan alur bisnis utama
            report($e);
            return null;
        }
    }
}

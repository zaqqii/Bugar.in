<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommunityVerificationController extends Controller
{
    public function index()
    {
        $verifications = Community::with('owner')
            ->where('verification_status', 'pending')
            ->whereNotNull('verification_requested_at')
            ->orderBy('verification_requested_at', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.verifications.index', compact('verifications'));
    }

    public function approve(Request $request, Community $community)
    {
        if ($community->verification_status !== 'pending') {
            return back()->with('error', 'Aksi ditolak: permohonan hanya dapat diproses untuk komunitas berstatus pending.');
        }

        $oldValues = [
            'verification_status' => $community->verification_status,
            'verified_at'         => $community->verified_at,
            'rejection_reason'    => $community->rejection_reason,
        ];

        DB::transaction(function () use ($community, $oldValues, $request) {
            $community->verification_status = 'verified';
            $community->verified_at         = now();
            $community->rejection_reason    = null;
            $community->save();

            DB::table('audit_logs')->insert([
                'user_id'        => Auth::id(),
                'action'         => 'community.verification_approved',
                'auditable_type' => 'community',
                'auditable_id'   => $community->id,
                'old_values'     => json_encode($oldValues),
                'new_values'     => json_encode([
                    'verification_status' => 'verified',
                    'verified_at'         => $community->verified_at,
                ]),
                'ip_address'     => $request->ip(),
                'user_agent'     => substr((string) $request->userAgent(), 0, 255),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        return back()->with('success', "Permohonan verifikasi komunitas \"{$community->name}\" berhasil disetujui.");
    }

    public function reject(Request $request, Community $community)
    {
        if ($community->verification_status !== 'pending') {
            return back()->with('error', 'Aksi ditolak: permohonan hanya dapat ditolak untuk komunitas berstatus pending.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.min'      => 'Alasan penolakan minimal 10 karakter agar tampil jelas ke penyedia komunitas.',
        ]);

        $oldValues = [
            'verification_status' => $community->verification_status,
            'rejection_reason'    => $community->rejection_reason,
        ];

        DB::transaction(function () use ($community, $validated, $oldValues, $request) {
            $community->verification_status = 'rejected';
            $community->rejection_reason    = trim($validated['rejection_reason']);
            $community->verified_at         = null;
            $community->save();

            DB::table('audit_logs')->insert([
                'user_id'        => Auth::id(),
                'action'         => 'community.verification_rejected',
                'auditable_type' => 'community',
                'auditable_id'   => $community->id,
                'old_values'     => json_encode($oldValues),
                'new_values'     => json_encode([
                    'verification_status' => 'rejected',
                    'rejection_reason'    => $community->rejection_reason,
                ]),
                'ip_address'     => $request->ip(),
                'user_agent'     => substr((string) $request->userAgent(), 0, 255),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        return back()->with('success', "Permohonan verifikasi komunitas \"{$community->name}\" telah ditolak.");
    }
}
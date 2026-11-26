<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityBlock;
use App\Models\CommunityMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{
    /**
     * Tampilkan halaman detail komunitas.
     */
    public function show($id)
    {
        $community = Community::where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('slug', $id);
            })
            ->where('is_active', true)
            ->with(['owner', 'schedules' => function ($q) {
                $today = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
                $q->where('is_active', true)
                  ->whereNull('cancelled_at')
                  ->whereNotIn('status', ['cancelled', 'completed'])
                  ->whereDate('date', '>=', $today)
                  ->orderBy('date', 'asc')
                  ->orderBy('start_time', 'asc');
            }, 'events' => function ($q) {
                $q->where('is_active', true)
                  ->active()
                  ->notFinished()
                  ->orderBy('event_date', 'asc');
            }, 'members.user'])
            ->withCount([
                'members',
                'followers',
                'events' => function ($q) {
                    $q->where('is_active', true)
                      ->active()
                      ->notFinished();
                }
            ])
            ->firstOrFail();

        $isMember = auth()->check()
            ? $community->members()->where('user_id', auth()->id())->where('status', 'active')->exists()
            : false;

        $isFollower = auth()->check()
            ? $community->followers()->where('user_id', auth()->id())->exists()
            : false;

        $similarCommunities = Community::where('id', '!=', $community->id)
            ->where('is_active', true)
            ->where(function ($q) use ($community) {
                $q->where('sport_category', $community->sport_category)
                  ->orWhere('city', $community->city);
            })
            ->take(2)
            ->get();

        if ($similarCommunities->isEmpty()) {
            $similarCommunities = Community::where('id', '!=', $community->id)
                ->where('is_active', true)
                ->take(2)
                ->get();
        }

        $joinedScheduleIds = auth()->check()
            ? \App\Models\Participant::where('user_id', auth()->id())
                ->where('participatable_type', \App\Models\Schedule::class)
                ->whereIn('status', ['registered', 'attended'])
                ->pluck('participatable_id')
                ->all()
            : [];

        return view('user.community-detail', compact('community', 'isMember', 'isFollower', 'similarCommunities', 'joinedScheduleIds'));
    }

    public function join($id)
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if (! $user instanceof User) {
            $user = User::find(auth()->id());
        }

        $community = Community::where(function ($q) use ($id) {
            $q->where('id', $id)
              ->orWhere('slug', $id);
        })->first();

        if (! $community || ! $community->is_active) {
            abort(404);
        }

        // 1. Cek apakah user terkena join_blocked_until
        if ($user->isJoinBlocked()) {
            $until = $user->joinBlockedUntilFormatted() ?? $user->join_blocked_until->timezone(config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d F Y, H:i');
            return back()->with('error', "Pendaftaran bergabung komunitas untuk akun Anda sedang dibatasi hingga {$until} WIB karena akumulasi ketidakhadiran (no-show).");
        }

        // 2. Cek apakah user diblokir oleh komunitas ini
        if ($community->isUserBlocked($user)) {
            return back()->with('error', 'Anda tidak dapat bergabung karena telah diblokir dari komunitas ini.');
        }

        return DB::transaction(function () use ($community, $user) {
            $lockedCommunity = Community::where('id', $community->id)->lockForUpdate()->firstOrFail();

            // 3. Cek status keanggotaan user di komunitas ini
            $member = CommunityMember::where('user_id', $user->id)
                ->where('community_id', $lockedCommunity->id)
                ->lockForUpdate()
                ->first();

            if ($member && $member->status === 'banned') {
                return back()->with('error', 'Anda telah diblokir dari komunitas ini dan tidak dapat bergabung kembali.');
            }

            if ($member && $member->status === 'active') {
                return back()->with('info', 'Kamu sudah menjadi anggota komunitas ini.');
            }

            if ($member && $member->status === 'left') {
                $member->status = 'active';
                $member->joined_at = now();
                $member->left_at = null;
                $member->save();

                $lockedCommunity->syncMembersCount();

                return back()->with('success', 'Berhasil bergabung kembali dengan komunitas!');
            }

            CommunityMember::create([
                'user_id' => $user->id,
                'community_id' => $lockedCommunity->id,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $lockedCommunity->syncMembersCount();

            return back()->with('success', 'Berhasil bergabung dengan komunitas!');
        });
    }
}
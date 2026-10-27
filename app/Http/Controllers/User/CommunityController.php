<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Community;
use App\Models\CommunityMember;

class CommunityController extends Controller
{
    /**
     * Tampilkan halaman detail komunitas.
     */
    public function show($id)
    {
        $community = Community::where('id', $id)
            ->orWhere('slug', $id)
            ->with(['owner', 'events' => function ($q) {
                $q->where('is_active', true)->orderBy('event_date', 'asc');
            }, 'members.user'])
            ->withCount(['members', 'followers', 'events'])
            ->firstOrFail();

        $isMember = auth()->check()
            ? $community->members()->where('user_id', auth()->id())->where('status', 'active')->exists()
            : false;

        $isFollower = auth()->check()
            ? $community->followers()->where('user_id', auth()->id())->exists()
            : false;

        return view('user.community-detail', compact('community', 'isMember', 'isFollower'));
    }

    public function join($id)
    {
        // Cek apakah user sudah pernah bergabung sebelumnya
        $exists = CommunityMember::where('user_id', auth()->id())
                                 ->where('community_id', $id)
                                 ->exists();

        if (!$exists) {
            CommunityMember::create([
                'user_id' => auth()->id(),
                'community_id' => $id,
                'status' => 'active',
                'joined_at' => now(),
            ]);
            return back()->with('success', 'Berhasil bergabung dengan komunitas!');
        }

        return back()->with('info', 'Kamu sudah menjadi anggota komunitas ini.');
    }
}
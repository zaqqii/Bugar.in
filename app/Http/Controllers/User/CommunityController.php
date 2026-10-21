<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommunityMember;

class CommunityController extends Controller
{
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
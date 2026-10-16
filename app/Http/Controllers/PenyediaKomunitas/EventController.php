<?php

namespace App\Http\Controllers\PenyediaKomunitas;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $community = Community::where('owner_id', $user->id)->first();

        return view('penyedia-komunitas.events.create', compact('community'));
    }
}

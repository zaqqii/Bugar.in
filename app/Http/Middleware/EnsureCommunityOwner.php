<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommunityOwner
{
    /**
     * Hanya izinkan user dengan role 'community_owner' untuk akses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'community_owner') {
            abort(403, 'Akses hanya untuk Penyedia Komunitas.');
        }

        return $next($request);
    }
}

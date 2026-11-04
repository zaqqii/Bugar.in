<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->guest(route('admin.login'));
        }

        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Akun ini tidak memiliki akses administrator.']);
        }

        return $next($request);
    }
}

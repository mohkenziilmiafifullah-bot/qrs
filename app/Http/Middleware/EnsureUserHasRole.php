<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage in routes: ->middleware('role:sales') or ->middleware('role:admin')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== $role) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->status !== 'active') {
            abort(403, 'Akun Anda tidak aktif. Hubungi admin.');
        }

        return $next($request);
    }
}

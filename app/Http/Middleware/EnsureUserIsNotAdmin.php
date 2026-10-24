<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotAdmin
{
    /**
     * Halaman untuk pengunjung, pembeli, dan agency (mis. katalog) tidak dibuka oleh admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin()) {
            abort(403, 'Halaman ini tidak tersedia untuk akun admin. Kelola listing melalui menu Kelola Data.');
        }

        return $next($request);
    }
}

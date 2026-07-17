<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Intercept Spatie's UnauthorizedException and redirect to dashboard with error flash
 * instead of throwing a raw 403 HTML page.
 */
class HandlePermissionDenied
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (UnauthorizedException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }
}

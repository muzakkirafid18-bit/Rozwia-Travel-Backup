<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // pastikan akun masih aktif
        if ($user->status_akun !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda berstatus non-aktif. Silakan hubungi admin.',
            ]);
        }

        // pastikan rolenya sesuai
        if (!in_array($user->role, $roles)) {
            abort(403, 'Akses Ditolak: Role ' . strtoupper($user->role) . ' tidak diizinkan membuka halaman ini.');
        }

        return $next($request);
    }
}

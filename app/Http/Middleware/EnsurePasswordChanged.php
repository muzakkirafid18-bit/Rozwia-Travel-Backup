<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengeluarkan sesi aktif apabila password akun di-reset oleh Administrator,
 * serta memaksa user yang login memakai password sementara untuk menggantinya.
 */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $request->hasSession()) {
            $sessionSig = $request->session()->get('session_auth_sig');
            $currentHash = (string) $user->getAuthPassword();

            if (is_array($sessionSig) && ($sessionSig['id'] ?? null) === $user->id) {
                if (!hash_equals((string) ($sessionSig['hash'] ?? ''), $currentHash)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()
                        ->route('login')
                        ->withErrors([
                            'email' => 'Password akun Anda telah di-reset oleh Administrator. Silakan login kembali menggunakan password sementara dari email Anda.',
                        ]);
                }
            } elseif (!$user->must_change_password) {
                $request->session()->put('session_auth_sig', [
                    'id'   => $user->id,
                    'hash' => $currentHash,
                ]);
            }
        }

        if ($user && $user->must_change_password
            && !$request->routeIs('password.force.*', 'logout')) {
            return redirect()->route('password.force.show');
        }

        return $next($request);
    }
}

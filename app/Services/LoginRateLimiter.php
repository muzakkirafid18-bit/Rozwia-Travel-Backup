<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter as FortifyLoginRateLimiter;

/**
 * Pembatas percobaan login gagal: maksimal 3 kali salah, blokir sementara 5 menit (300 detik).
 */
class LoginRateLimiter extends FortifyLoginRateLimiter
{
    public const MAX_ATTEMPTS = 3;
    public const DECAY_SECONDS = 300;

    public function tooManyAttempts(Request $request)
    {
        return $this->limiter->tooManyAttempts($this->throttleKey($request), self::MAX_ATTEMPTS);
    }

    public function increment(Request $request)
    {
        $this->limiter->hit($this->throttleKey($request), self::DECAY_SECONDS);

        // kalau sudah 3x gagal, langsung blokir 5 menit
        if ($this->tooManyAttempts($request)) {
            $seconds = max(1, (int) $this->availableIn($request));
            throw ValidationException::withMessages([
                Fortify::username() => [self::lockoutMessage($seconds)],
            ])->status(429);
        }
    }

    public static function lockoutMessage(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remSeconds = $seconds % 60;

        if ($minutes > 0 && $remSeconds > 0) {
            $wait = "{$minutes} menit {$remSeconds} detik";
        } elseif ($minutes > 0) {
            $wait = "{$minutes} menit";
        } else {
            $wait = "{$remSeconds} detik";
        }

        return "Anda telah salah memasukkan email atau password sebanyak 3 kali. Akses login diblokir sementara selama {$wait}.";
    }

    protected function throttleKey(Request $request)
    {
        return Str::transliterate('login_attempt|' . $request->ip());
    }
}

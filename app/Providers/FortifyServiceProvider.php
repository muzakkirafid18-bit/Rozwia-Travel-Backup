<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use App\Services\LoginRateLimiter as CustomLoginRateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LockoutResponse as LockoutResponseContract;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter as FortifyLoginRateLimiter;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FortifyLoginRateLimiter::class, CustomLoginRateLimiter::class);

        $this->app->singleton(LockoutResponseContract::class, function ($app) {
            return new class($app->make(FortifyLoginRateLimiter::class)) implements LockoutResponseContract {
                public function __construct(private FortifyLoginRateLimiter $limiter)
                {
                }

                public function toResponse($request)
                {
                    $seconds = max(1, (int) $this->limiter->availableIn($request));
                    throw ValidationException::withMessages([
                        Fortify::username() => [CustomLoginRateLimiter::lockoutMessage($seconds)],
                    ])->status(429);
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Blade Views
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));

        // cek login pakai email & pastikan status akun aktif
        Fortify::authenticateUsing(function (Request $request) {
            $loginInput = trim((string) $request->input('email'));
            $user = User::where('email', $loginInput)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if ($user->status_akun !== 'active') {
                    throw ValidationException::withMessages([
                        Fortify::username() => ['Akun Anda berstatus non-aktif. Silakan hubungi admin atau selesaikan pembayaran DP.'],
                    ]);
                }

                return $user;
            }

            return null;
        });

        // batasi salah login 3x dalam 5 menit per email + IP
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower(trim((string) $request->input(Fortify::username(), '')));
            $throttleKey = Str::transliterate('login_attempt|' . $email . '|' . $request->ip());

            return Limit::perMinutes(5, 3)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip()
            );
        });
    }
}

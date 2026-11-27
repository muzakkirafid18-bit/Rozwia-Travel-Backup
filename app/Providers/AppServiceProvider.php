<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof \App\Models\User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
                if (request() && request()->hasSession()) {
                    request()->session()->put('session_auth_sig', [
                        'id'   => $event->user->id,
                        'hash' => (string) $event->user->getAuthPassword(),
                    ]);
                }
            }
        });
    }
}

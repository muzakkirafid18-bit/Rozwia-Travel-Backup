<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForcePasswordController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (!$request->user()->must_change_password) {
            return redirect()->route('home');
        }

        return view('auth.force-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                'not_in:Jamaah123!,jamaah123!,Password123!,12345678',
                Password::min(10)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password baru.',
            'password.not_in'    => 'Password terlalu umum, silakan gunakan kombinasi lain.',
        ]);

        if (Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['password' => 'Password baru tidak boleh sama dengan password sementara.']);
        }

        // larang password yang mirip nama atau email sendiri
        $plain = strtolower($request->input('password'));
        $emailName = strtolower(strtok($user->email, '@'));
        if ((strlen($emailName) >= 4 && str_contains($plain, $emailName))) {
            return back()->withErrors(['password' => 'Password tidak boleh mengandung bagian dari email Anda.']);
        }

        $user->forceFill([
            'password'             => Hash::make($request->input('password')),
            'must_change_password' => false,
            'password_changed_at'  => now(),
        ])->save();

        // logout sesi lain & refresh session id
        Auth::logoutOtherDevices($request->input('password'));
        $request->session()->regenerate();
        $request->session()->put('session_auth_sig', [
            'id'   => $user->id,
            'hash' => (string) $user->getAuthPassword(),
        ]);

        return redirect()->route('home')
            ->with('success', 'Alhamdulillah! Password Anda berhasil diperbarui. Akun Anda kini lebih aman.');
    }
}

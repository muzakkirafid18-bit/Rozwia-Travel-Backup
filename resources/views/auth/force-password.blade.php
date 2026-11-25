@extends('layouts.app')

@section('title', 'Wajib Ganti Password - ROZWIA')

@section('content')
<div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#0B3B24]/90 backdrop-blur-sm font-sans overflow-y-auto"
     x-data="{
        pwd: '', show: false,
        get checks() {
            return {
                len: this.pwd.length >= 10,
                lower: /[a-z]/.test(this.pwd),
                upper: /[A-Z]/.test(this.pwd),
                num: /[0-9]/.test(this.pwd),
                sym: /[^A-Za-z0-9]/.test(this.pwd),
            };
        },
        get score() { return Object.values(this.checks).filter(Boolean).length; },
        get valid() { return this.score === 5; }
     }">
    <div class="bg-white rounded-[32px] p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-5 my-8">
        <div class="text-center space-y-2">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-[#0B3B24] grid place-items-center p-2.5 shadow-md">
                <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-bold text-[#0B3B24] tracking-tight">Buat Password Baru</h1>
            <p class="text-xs text-stone-600">Anda masih menggunakan <b>password sementara</b>. Demi keamanan data pribadi & dokumen ibadah, Anda <b>wajib</b> membuat password baru sebelum melanjutkan.</p>
        </div>

        @if ($errors->any())
            <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p class="flex items-center gap-1.5"><x-icon name="warning" class="w-4 h-4 shrink-0 text-rose-600" /><span>{{ $error }}</span></p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.force.update') }}" class="space-y-4 text-xs" autocomplete="off" x-data="{ pwdConfirm: '' }">
            @csrf
            <div class="space-y-1">
                <label class="font-bold text-stone-700">Password Baru</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password" x-model="pwd" required autocomplete="new-password" placeholder="Masukkan password baru" class="w-full px-4 py-2.5 pr-12 rounded-xl border border-stone-300 focus:outline-none focus:border-[#0B3B24]">
                    <button type="button" @click="show = !show" :aria-label="show ? 'Sembunyikan' : 'Lihat'" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#0B3B24] hover:text-emerald-900 cursor-pointer flex items-center">
                        <span x-show="!show"><x-icon name="eye" class="w-4 h-4" /></span>
                        <span x-show="show" x-cloak><x-icon name="eye_off" class="w-4 h-4" /></span>
                    </button>
                </div>
                <div class="h-1.5 w-full bg-stone-200 rounded-full overflow-hidden mt-1.5">
                    <div class="h-full transition-all" :style="'width:' + (score/5*100) + '%'" :class="score < 3 ? 'bg-rose-500' : (score < 5 ? 'bg-amber-500' : 'bg-emerald-600')"></div>
                </div>
            </div>

            <div class="space-y-1">
                <label class="font-bold text-stone-700">Konfirmasi Password</label>
                <input :type="show ? 'text' : 'password'" name="password_confirmation" x-model="pwdConfirm" required autocomplete="new-password" placeholder="Ulangi password baru" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-[#0B3B24]">
                <p x-show="pwdConfirm.length > 0 && pwd !== pwdConfirm" x-cloak class="text-[11px] text-rose-600 font-semibold mt-1 flex items-center gap-1">
                    <x-icon name="warning" class="w-3.5 h-3.5 shrink-0" /> <span>Konfirmasi password belum sama.</span>
                </p>
            </div>

            <ul class="grid grid-cols-2 gap-1.5 text-[11px] p-3 rounded-2xl bg-stone-50 border border-stone-200">
                <li :class="checks.len ? 'text-emerald-700 font-medium' : 'text-stone-500'" class="flex items-center gap-1.5">
                    <x-icon name="check_circle" class="w-3.5 h-3.5 shrink-0" /> <span>Minimal 10 karakter</span>
                </li>
                <li :class="checks.upper ? 'text-emerald-700 font-medium' : 'text-stone-500'" class="flex items-center gap-1.5">
                    <x-icon name="check_circle" class="w-3.5 h-3.5 shrink-0" /> <span>Huruf besar (A-Z)</span>
                </li>
                <li :class="checks.lower ? 'text-emerald-700 font-medium' : 'text-stone-500'" class="flex items-center gap-1.5">
                    <x-icon name="check_circle" class="w-3.5 h-3.5 shrink-0" /> <span>Huruf kecil (a-z)</span>
                </li>
                <li :class="checks.num ? 'text-emerald-700 font-medium' : 'text-stone-500'" class="flex items-center gap-1.5">
                    <x-icon name="check_circle" class="w-3.5 h-3.5 shrink-0" /> <span>Angka (0-9)</span>
                </li>
                <li :class="checks.sym ? 'text-emerald-700 font-medium' : 'text-stone-500'" class="flex items-center gap-1.5">
                    <x-icon name="check_circle" class="w-3.5 h-3.5 shrink-0" /> <span>Simbol (!@#$...)</span>
                </li>
            </ul>

            <button type="submit" :disabled="!valid || pwd !== pwdConfirm" :class="(valid && pwd === pwdConfirm) ? 'bg-[#0B3B24] hover:bg-emerald-900 cursor-pointer' : 'bg-stone-300 cursor-not-allowed'" class="w-full text-white font-bold py-3 rounded-xl shadow-md transition">
                Simpan Password Baru &amp; Lanjutkan
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="text-[11px] text-stone-500 hover:text-rose-600 underline cursor-pointer">Keluar</button>
        </form>
    </div>
</div>
@endsection

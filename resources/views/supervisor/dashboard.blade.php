@extends('layouts.supervisor')

@section('title', 'Dashboard Supervisor - ROZWIA Islamic Travel')

@section('content')
@php
    $chartMax = max(5, (int) collect($monthlyStats)->flatMap(fn ($s) => [$s['umroh'], $s['haji']])->max());
@endphp

<!-- Banner Utama: Selamat Datang di Dashboard Supervisor -->
<div class="bg-[#0B3B24] text-white rounded-[32px] p-8 sm:p-10 shadow-sm flex flex-col md:flex-row items-center gap-8 relative overflow-hidden">
    <div class="w-24 h-24 rounded-2xl bg-emerald-950/80 border border-emerald-800/60 flex items-center justify-center shrink-0 p-4">
        <img src="{{ asset('images/lOGO rozwia.png') }}" alt="Logo" class="w-full h-full object-contain">
    </div>
    <div class="space-y-2">
        <h1 class="text-2xl sm:text-3xl font-[800] font-['Abhaya_Libre'] flex items-center gap-2">
            Selamat Datang di Dashboard Supervisor
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 128 128" aria-hidden="true" class="shrink-0">
                <path d="M0 0h128v128H0z" fill="none" />
                <path fill="#ffca28" d="M93.3 60c.2.3.7.1.7-.2c.6-5.4 2.2-20.3 12.8-23.5c3.4-1 6.8 1.4 7.2 4.5c.7 5-5 17.9-4.5 29.7c.1 1.9 3.3 22-5.2 33.9s-28.7 24-48.8 5.6c-10.4-9.5-10.4-13.3-23.3-26.6c-2.6-2.6-13-14-15.8-17.5c-3.7-4.7 2.2-10.9 6.7-7.7c2.1 1.5 20.7 17.1 21.4 17.8c1.4 1.2 3.1-.5 2.1-1.7c-11.4-15-22.4-28.5-25.4-33.7s4.2-10.8 8.4-6.3c2.9 3 24.4 28.1 25.4 29.2s2.7-.3 2.1-1.7c-.7-1.3-18.1-31.6-21.2-38.5c-2.7-6.1 6.3-11.8 10.5-5.5c3.4 5 22.4 36.6 23.1 37.7c.9 1.6 2.9.6 2.1-1.2C71 53 59.3 21 58.4 17.7c-1.6-5.8 6.7-10.6 10.6-4c5.3 9.1 15.8 36.9 24.3 46.3" />
                <path fill="#b0bec5" d="M10.6 81.3c3.1 9.2 7.8 14.9 15.5 20.1c1.4 1 .6 3-1 2.3c-7.2-2.9-15.2-9.3-17.1-22c-.3-1.7 2.1-2 2.6-.4m6-5.5c3.1 9.2 7.8 14.9 15.5 20.1c1.4 1 .6 3-1 2.3c-7.2-2.9-15.2-9.3-17.1-22c-.2-1.7 2.1-2 2.6-.4m76.9-41.2c-2.4-9.4-6.8-15.4-14.1-21c-1.4-1-.4-3 1.2-2.3c7 3.3 14.6 10.3 15.6 23.1c.1 1.7-2.3 1.9-2.7.2m6.5-4.8c-2.4-9.4-6.8-15.4-14.1-21c-1.4-1-.4-3 1.2-2.3c7 3.3 14.6 10.3 15.6 23.1c.1 1.6-2.2 1.8-2.7.2" />
            </svg>
        </h1>
        <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed font-sans">
            Kelola seluruh proses perjalanan jamaah mulai dari pemantauan pendaftaran, pengelolaan paket Haji &amp; Umroh, pemantauan akun pengguna, hingga laporan keuangan real-time dalam satu dashboard terpadu.
        </p>
    </div>
</div>

<!-- 3 kartu statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Kartu 1: Jumlah Paket Tersedia -->
    <div class="bg-white rounded-[32px] p-7 border border-stone-200 shadow-sm flex flex-col justify-between space-y-6">
        <div class="space-y-1">
            <span class="text-xs text-stone-500 font-sans block">Jumlah Paket Tersedia</span>
            <h3 class="text-3xl font-[800] text-stone-900 font-['Abhaya_Libre']">{{ $totalPaket }} Paket</h3>
        </div>
        <div class="flex justify-between items-end border-t border-stone-100 pt-4">
            <span class="text-xs text-stone-600 font-bold">Kelola Paket</span>
            <a href="{{ route('supervisor.paket') }}" class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-800 flex items-center justify-center text-sm font-bold hover:bg-amber-100 transition">
                <x-icon name="inventory_2" class="w-5 h-5" />
            </a>
        </div>
    </div>

    <!-- Kartu 2: Jumlah Total Pendapatan -->
    <div class="bg-white rounded-[32px] p-7 border border-stone-200 shadow-sm flex flex-col justify-between space-y-6">
        <div class="space-y-1">
            <span class="text-xs text-stone-500 font-sans block">Jumlah Total Pendapatan</span>
            <h3 class="text-xl sm:text-2xl font-[800] text-stone-900 font-['Abhaya_Libre']">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
        </div>
        <div class="flex justify-between items-end border-t border-stone-100 pt-4">
            <span class="text-xs text-stone-600 font-bold">Lihat Pendapatan</span>
            <a href="{{ route('supervisor.biaya') }}" class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-800 font-[800] flex items-center justify-center text-xs font-['Abhaya_Libre'] shadow-xs hover:bg-emerald-100 transition">
                Rp
            </a>
        </div>
    </div>

    <!-- Kartu 3: Daftar Akun Terdaftar -->
    <div class="bg-white rounded-[32px] p-7 border border-stone-200 shadow-sm flex flex-col justify-between space-y-6">
        <div class="space-y-1">
            <span class="text-xs text-stone-500 font-sans block">Daftar Akun Terdaftar</span>
            <h3 class="text-3xl font-[800] text-stone-900 font-['Abhaya_Libre']">{{ $displayTotalAkun }} Jamaah</h3>
        </div>
        <div class="flex justify-between items-end border-t border-stone-100 pt-4">
            <span class="text-xs text-stone-600 font-bold">Lihat Daftar Akun</span>
            <a href="{{ route('supervisor.users') }}" class="w-10 h-10 rounded-full bg-amber-50 text-amber-800 flex items-center justify-center text-sm hover:bg-amber-100 transition">
                <x-icon name="group" class="w-5 h-5" />
            </a>
        </div>
    </div>

</div>

<!-- Bagian Bawah: Grafik Pendaftaran & Status -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- grafik pendaftaran -->
    <div class="lg:col-span-7 bg-white rounded-[32px] p-6 sm:p-7 border border-stone-200 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-2">
                <x-icon name="group" class="w-5 h-5 text-[#0B3B24]" />
                <h3 class="text-base font-[800] font-['Abhaya_Libre'] text-stone-900">Jumlah Pendaftaran Jamaah</h3>
            </div>

            <!-- Legenda -->
            <div class="flex items-center gap-4">
                <div class="bg-[#F9F6F0] border border-stone-200/80 px-4 py-2 rounded-2xl flex items-center gap-4 text-xs font-[700] font-sans">
                    <span class="flex items-center gap-2 text-stone-800">
                        <span class="w-3 h-3 rounded-full bg-[#0B3B24] inline-block"></span> Umroh
                    </span>
                    <span class="flex items-center gap-2 text-stone-600">
                        <span class="w-3 h-3 rounded-full bg-[#659B81] inline-block"></span> Haji
                    </span>
                </div>
            </div>
        </div>

        <!-- chart batang bulanan -->
        <div class="pt-6 pb-2 space-y-4">
            <div class="h-56 border-b border-stone-300 relative flex justify-around items-end px-2 sm:px-6">
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-25">
                    <div class="border-b border-stone-400 w-full"></div>
                    <div class="border-b border-stone-400 w-full"></div>
                    <div class="border-b border-stone-400 w-full"></div>
                    <div class="border-b border-stone-400 w-full"></div>
                    <div class="border-b border-stone-400 w-full"></div>
                </div>

                @foreach($monthlyStats as $stat)
                    @php
                        $umrohPct = $chartMax > 0 ? max(6, round(($stat['umroh'] / $chartMax) * 100)) : 6;
                        $hajiPct  = $chartMax > 0 ? max(6, round(($stat['haji'] / $chartMax) * 100)) : 6;
                    @endphp
                    <div class="flex flex-col items-center gap-1.5 h-full justify-end z-10">
                        <span class="text-[11px] font-bold text-stone-800 font-sans">{{ $stat['umroh'] }}</span>
                        <div class="w-5 sm:w-6 bg-[#0B3B24] rounded-t-sm transition-all" style="height: {{ $stat['umroh'] > 0 ? $umrohPct : 6 }}%;" title="Umroh: {{ $stat['umroh'] }} jamaah"></div>
                        <span class="text-[11px] font-bold text-stone-800 font-sans">{{ $stat['haji'] }}</span>
                        <div class="w-5 sm:w-6 bg-[#659B81] rounded-t-sm transition-all" style="height: {{ $stat['haji'] > 0 ? $hajiPct : 6 }}%;" title="Haji: {{ $stat['haji'] }} jamaah"></div>
                    </div>
                @endforeach
            </div>

            <!-- Label Bulan -->
            <div class="flex justify-around items-center text-[10px] sm:text-xs text-stone-500 font-bold font-sans pt-2 px-1">
                @foreach($monthlyStats as $stat)
                    <span>{{ $stat['bulan'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- status paket & akun -->
    <div class="lg:col-span-5 space-y-6">
        <div class="bg-white rounded-[32px] p-6 border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-3">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-800 flex items-center justify-center text-base">
                    <x-icon name="inventory_2" class="w-5 h-5" />
                </div>
                <h4 class="text-sm font-[800] font-['Abhaya_Libre'] text-stone-900">Jumlah Paket Berdasarkan Status</h4>
            </div>
            <div class="space-y-3 text-xs font-sans">
                <div class="flex justify-between items-center bg-stone-50 p-3 rounded-2xl border border-stone-100">
                    <span class="flex items-center gap-2 text-stone-700 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-800"></span> Aktif
                    </span>
                    <strong class="text-stone-900">{{ $paketAktif }} Paket</strong>
                </div>
                <div class="flex justify-between items-center bg-stone-50 p-3 rounded-2xl border border-stone-100">
                    <span class="flex items-center gap-2 text-stone-700 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Non Aktif
                    </span>
                    <strong class="text-stone-900">{{ $paketNonaktif }} Paket</strong>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[32px] p-6 border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-3">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-800 flex items-center justify-center text-base">
                    <x-icon name="group" class="w-5 h-5" />
                </div>
                <h4 class="text-sm font-[800] font-['Abhaya_Libre'] text-stone-900">Jumlah Akun Berdasarkan Status</h4>
            </div>
            <div class="space-y-3 text-xs font-sans">
                <div class="flex justify-between items-center bg-stone-50 p-3 rounded-2xl border border-stone-100">
                    <span class="flex items-center gap-2 text-stone-700 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-800"></span> Aktif
                    </span>
                    <strong class="text-stone-900">{{ $displayAkunAktif }} Akun</strong>
                </div>
                <div class="flex justify-between items-center bg-stone-50 p-3 rounded-2xl border border-stone-100">
                    <span class="flex items-center gap-2 text-stone-700 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Non Aktif
                    </span>
                    <strong class="text-stone-900">{{ $displayAkunNonaktif }} Akun</strong>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

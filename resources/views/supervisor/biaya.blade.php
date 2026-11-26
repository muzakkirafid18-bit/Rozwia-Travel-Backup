@extends('layouts.supervisor')

@section('title', 'Report Total Biaya - Supervisor ROZWIA')

@section('content')
<div class="space-y-6 font-serif">

    <!-- judul halaman -->
    <h1 class="text-3xl font-bold text-[#0B3B24]">Report Total Biaya</h1>

    <!-- banner laporan -->
    <div class="bg-[#0B3B24] text-white rounded-[32px] p-6 sm:p-8 shadow-sm flex items-center gap-6">
        <div class="w-16 h-16 rounded-2xl bg-emerald-900 border border-emerald-700 flex items-center justify-center text-3xl shrink-0">
            <x-icon name="mosque" class="w-8 h-8 text-amber-400" />
        </div>
        <div class="space-y-1">
            <h2 class="text-xl sm:text-2xl font-bold text-[#E5D3B3]">Laporan Jamaah &amp; Keuangan</h2>
            <p class="text-xs sm:text-sm text-emerald-100/80 font-sans">
                Pusat informasi data jamaah, rekapitulasi, dan laporan keuangan real-time.
            </p>
        </div>
    </div>

    <!-- filter rentang tanggal -->
    <div class="bg-white rounded-[32px] p-4 sm:p-5 shadow-sm border border-stone-200 flex flex-col lg:flex-row items-center justify-between gap-4 font-sans text-xs">
        <form action="{{ route('supervisor.biaya') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <input type="hidden" name="search" value="{{ $search }}">

            <!-- tanggal mulai -->
            <div class="flex items-center gap-2 bg-[#FAF8F5] border border-stone-300 rounded-2xl px-4 py-2 flex-1 sm:flex-initial">
                <x-icon name="calendar_month" class="w-5 h-5 text-stone-500" />
                <div class="flex flex-col">
                    <label for="tanggal_mulai" class="text-[10px] text-stone-400 font-bold uppercase cursor-pointer">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ $rawTglMulai ?? '' }}"
                           class="bg-transparent font-bold text-stone-800 text-xs focus:outline-none cursor-pointer">
                </div>
            </div>

            <!-- tanggal akhir -->
            <div class="flex items-center gap-2 bg-[#FAF8F5] border border-stone-300 rounded-2xl px-4 py-2 flex-1 sm:flex-initial">
                <x-icon name="calendar_month" class="w-5 h-5 text-stone-500" />
                <div class="flex flex-col">
                    <label for="tanggal_akhir" class="text-[10px] text-stone-400 font-bold uppercase cursor-pointer">Tanggal Akhir</label>
                    <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="{{ $rawTglAkhir ?? '' }}"
                           class="bg-transparent font-bold text-stone-800 text-xs focus:outline-none cursor-pointer">
                </div>
            </div>

            <!-- tombol terapkan -->
            <button type="submit" class="bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold px-6 py-3 rounded-2xl transition cursor-pointer shadow-sm">
                Terapkan
            </button>

            @if(($hasDateFilter ?? false) || !empty($search))
                <a href="{{ route('supervisor.biaya') }}" class="px-5 py-3 rounded-2xl border border-stone-300 text-stone-700 font-bold hover:bg-stone-50 transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- info rentang tanggal -->
        <div class="text-stone-600 font-semibold px-2">
            @if($hasDateFilter ?? false)
                {{ $rawTglMulai ? \Carbon\Carbon::parse($rawTglMulai)->format('d/m/Y') : 'Awal' }}
                -
                {{ $rawTglAkhir ? \Carbon\Carbon::parse($rawTglAkhir)->format('d/m/Y') : 'Sekarang' }}
            @else
                Semua Periode Data
            @endif
        </div>
    </div>

    <!-- ringkasan statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 font-serif">
        <!-- total jamaah -->
        <div class="bg-white p-6 sm:p-8 rounded-[32px] border border-stone-200 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center text-lg">
                <x-icon name="group" class="w-5 h-5" />
            </div>
            <span class="text-xs font-bold uppercase tracking-wider text-stone-500 font-sans block">Total Jamaah Terdaftar</span>
            <h3 class="text-4xl font-bold text-[#0B3B24]">{{ $totalJamaahSum }}</h3>
        </div>

        <!-- total pembayaran -->
        <div class="bg-white p-6 sm:p-8 rounded-[32px] border border-stone-200 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg">
                <x-icon name="payments" class="w-5 h-5" />
            </div>
            <span class="text-xs font-bold uppercase tracking-wider text-stone-500 font-sans block">Total Biaya Keseluruhan</span>
            <h3 class="text-3xl sm:text-4xl font-bold text-[#0B3B24]">Rp {{ number_format($totalBayarSum, 0, ',', '.') }}</h3>
        </div>
    </div>

    <!-- tabel ringkasan keuangan -->
    <div class="bg-white rounded-[32px] p-6 sm:p-8 shadow-sm border border-stone-200 space-y-6 font-sans">

        <!-- header tabel & pencarian -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-base font-bold text-[#0B3B24] font-serif">Ringkasan Data Paket</h3>
                <p class="text-xs text-stone-400">Menampilkan {{ $pakets->count() }} paket</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <!-- kotak pencarian -->
                <form action="{{ route('supervisor.biaya') }}" method="GET" class="relative flex-1 sm:w-64">
                    @if($rawTglMulai) <input type="hidden" name="tanggal_mulai" value="{{ $rawTglMulai }}"> @endif
                    @if($rawTglAkhir) <input type="hidden" name="tanggal_akhir" value="{{ $rawTglAkhir }}"> @endif
                    <span class="absolute left-3.5 top-2.5 text-stone-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari Nama Paket"
                           class="w-full pl-9 pr-4 py-2.5 rounded-full bg-stone-50 border border-stone-300 text-xs focus:outline-none focus:border-[#0B3B24]">
                </form>
                <!-- tombol export excel -->
                <a href="{{ route('supervisor.biaya.export', ['tanggal_mulai' => $rawTglMulai, 'tanggal_akhir' => $rawTglAkhir, 'search' => $search]) }}"
                   class="bg-white hover:bg-stone-50 text-[#0B3B24] border border-[#0B3B24] font-bold text-xs py-2.5 px-5 rounded-full transition cursor-pointer flex items-center gap-2 shadow-xs">
                    <x-icon name="download" class="w-4 h-4" />
                    <span>Download Laporan (Excel)</span>
                </a>
            </div>
        </div>

        <!-- tabel data -->
        <div class="overflow-x-auto rounded-2xl border-2 border-stone-300 shadow-xs">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-stone-100 text-stone-900 font-serif border-b-2 border-stone-300 divide-x-2 divide-stone-300">
                        <th class="p-4 font-extrabold text-sm">Paket</th>
                        <th class="p-4 font-extrabold text-sm text-center">Jumlah Jamaah</th>
                        <th class="p-4 font-extrabold text-sm text-right">Total Bayar (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-stone-200 text-stone-800">
                    @forelse($pakets as $p)
                    <tr class="hover:bg-stone-50/80 transition divide-x-2 divide-stone-200">
                        <td class="p-4">
                            <span class="font-extrabold text-stone-900 font-serif text-[15px] block">{{ $p['nama'] }}</span>
                        </td>
                        <td class="p-4 text-center font-extrabold text-stone-900 text-[15px] tabular-nums">
                            {{ $p['jumlah'] }} Orang
                        </td>
                        <td class="p-4 text-right font-extrabold text-[#0B3B24] text-[15px] tabular-nums">
                            Rp {{ number_format($p['total'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-8 text-center text-stone-400 font-semibold">
                            Tidak ada data paket untuk rentang tanggal yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

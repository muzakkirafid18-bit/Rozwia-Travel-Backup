@extends('layouts.dashboard')

@section('title', 'Status Perjalanan - ROZWIA')

@section('content')
@php
    $jadwal = $booking?->jadwal;
    $paket = $jadwal?->paket;
    $date = $jadwal?->tanggal_keberangkatan;
    $returnDate = $jadwal?->tanggal_pulang;
    $daysDiff = $date ? max(0, (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false)) : null;
    $room = $booking?->catatan ? str_replace('Tipe Kamar: ', '', $booking->catatan) : 'Belum ditentukan';
    $maskapaiName = $jadwal?->maskapai?->nama_maskapai ?? 'Menunggu konfirmasi maskapai';
    $isFlightConfirmed = filled($jadwal?->maskapai?->nama_maskapai);
    $isTitikKumpulConfirmed = filled($jadwal?->tempat_kumpul) || filled($jadwal?->tanggal_kumpul);

    $hotelList = isset($paketHotels) && $paketHotels->isNotEmpty() ? $paketHotels : collect();
    $jamaahList = $booking?->jamaahDetails ?? collect();

    $jamaahRows = $jamaahList->values()->map(function ($j, $idx) {
        $initials = collect(explode(' ', trim((string) $j->nama_lengkap)))
            ->filter()
            ->take(2)
            ->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))
            ->implode('') ?: 'J';

        $kemenag = \App\Models\JamaahDetail::normalizeStatus($j->status_kemenag);
        $visa = \App\Models\JamaahDetail::normalizeStatus($j->status_visa);
        $isPublished = ($kemenag === 'Terbit' && $visa === 'Terbit');

        return [
            'no'            => $idx + 1,
            'initials'      => $initials,
            'nama'          => $j->nama_lengkap,
            'sub'           => $idx === 0 ? 'Ketua Rombongan' : 'Anggota Jamaah',
            'paspor'        => $j->passport_number ?: '-',
            'kemenag'       => $kemenag,
            'visa'          => $visa,
            'nomor_visa'    => $j->nomor_visa ?: '',
            'tanggal_terbit'=> $j->tanggal_terbit_visa ? $j->tanggal_terbit_visa->format('d/m/Y') : '-',
            'is_published'  => $isPublished,
        ];
    });

    $totalJamaahCount = $jamaahRows->count();
    $publishedCount = $jamaahRows->where('is_published', true)->count();
    $processingCount = $totalJamaahCount - $publishedCount;
    $latestUpdate = $jamaahList->max('updated_at');
@endphp

<div class="min-h-screen bg-[#EAE0D0] lg:grid lg:grid-cols-[300px_minmax(0,1fr)]"
     x-data="{
        targetIso: '{{ $date ? $date->copy()->setTime(8, 0)->toIso8601String() : '' }}',
        days: '{{ $daysDiff ?? '-' }}',
        hours: '00',
        minutes: '00',
        filterStatus: 'semua',
        searchQuery: '',
        rows: {{ Js::from($jamaahRows) }},
        get filteredRows() {
            const q = this.searchQuery.trim().toLowerCase();
            return this.rows.filter((r) => {
                const matchStatus = this.filterStatus === 'semua'
                    || (this.filterStatus === 'terbit' && r.is_published)
                    || (this.filterStatus === 'diproses' && !r.is_published);
                const matchQuery = !q
                    || r.nama.toLowerCase().includes(q)
                    || r.paspor.toLowerCase().includes(q)
                    || r.nomor_visa.toLowerCase().includes(q);
                return matchStatus && matchQuery;
            });
        },
        initCountdown() {
            if (!this.targetIso) return;
            const update = () => {
                const diff = new Date(this.targetIso).getTime() - Date.now();
                if (diff <= 0) {
                    this.days = '0';
                    this.hours = '00';
                    this.minutes = '00';
                    return;
                }
                const totalMinutes = Math.floor(diff / 60000);
                const d = Math.floor(totalMinutes / (60 * 24));
                const h = Math.floor((totalMinutes % (60 * 24)) / 60);
                const m = totalMinutes % 60;
                this.days = String(d);
                this.hours = String(h).padStart(2, '0');
                this.minutes = String(m).padStart(2, '0');
            };
            update();
            setInterval(update, 30000);
        }
     }"
     x-init="initCountdown()">

    @include('user._sidebar', ['active' => 'perjalanan'])

    <main class="min-w-0 px-5 py-7 sm:px-8 lg:px-10 lg:py-9">
        <div class="mx-auto max-w-6xl space-y-6">

            {{-- Header --}}
            <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="font-serif-display text-3xl sm:text-4xl font-bold text-[#0B3B24] tracking-tight">
                        Status Perjalanan &amp; Keberangkatan
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-stone-600">
                        Jadwal penerbangan, titik kumpul, akomodasi hotel, serta status dokumen resmi rombongan Anda.
                    </p>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white border border-stone-200/90 px-4 py-2 text-xs font-bold text-[#063B25] shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>{{ $paket?->nama_paket ?? 'Belum Ada Paket' }}</span>
                        @if($booking?->kode_booking)
                            <span class="text-stone-400">&bull;</span>
                            <span class="text-stone-600">{{ $booking->kode_booking }}</span>
                        @endif
                    </span>
                    <a href="{{ route('user.dashboard') }}#notifikasi"
                       class="w-10 h-10 rounded-full bg-white border border-stone-200/90 shadow-2xs flex items-center justify-center text-[#D8A941] hover:bg-stone-50 transition shrink-0"
                       title="Lihat Notifikasi">
                        <x-icon name="notifications" class="w-5 h-5" />
                    </a>
                </div>
            </header>

            {{-- 1. Hero Banner Hijau Gelap (#063B25) --}}
            <section class="rounded-[24px] bg-[#0B3B24] px-6 py-5 sm:px-8 sm:py-6 text-white shadow-sm">
                <div class="flex flex-col justify-between gap-5 md:flex-row md:items-center">
                    <div class="space-y-1.5">
                        <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-emerald-200/90">
                            <x-icon name="calendar_month" class="w-4 h-4 text-[#E7C878]" />
                            <span>{{ $date ? 'JADWAL KEBERANGKATAN TERKONFIRMASI' : 'JADWAL KEBERANGKATAN' }}</span>
                        </p>
                        <h2 class="font-serif-display text-2xl sm:text-3xl font-bold text-white leading-snug">
                            {{ $date ? $date->translatedFormat('l') . ', ' . $date->format('d/m/Y') : 'Jadwal keberangkatan akan diinformasikan' }}
                        </h2>
                        @if($returnDate)
                            <p class="text-xs text-emerald-100/80">
                                Kepulangan: {{ $returnDate->format('d/m/Y') }} &bull; Status: <strong class="text-[#E7C878]">{{ $trackingStatus }}</strong>
                            </p>
                        @else
                            <p class="text-xs text-emerald-100/80">
                                Status saat ini: <strong class="text-[#E7C878]">{{ $trackingStatus }}</strong>
                            </p>
                        @endif
                    </div>

                    {{-- Countdown Boxes --}}
                    <div class="flex items-center gap-2.5 shrink-0">
                        <div class="min-w-[68px] rounded-xl bg-[#0D4A31] border border-white/15 px-4 py-2.5 text-center">
                            <span class="block font-serif-display text-2xl sm:text-3xl font-bold text-white leading-none" x-text="days">{{ $daysDiff ?? '-' }}</span>
                            <span class="mt-1 block text-[9px] font-bold uppercase tracking-widest text-emerald-200/80">HARI</span>
                        </div>
                        <span class="text-lg font-bold text-white/40">:</span>
                        <div class="min-w-[68px] rounded-xl bg-[#0D4A31] border border-white/15 px-4 py-2.5 text-center">
                            <span class="block font-serif-display text-2xl sm:text-3xl font-bold text-white leading-none" x-text="hours">00</span>
                            <span class="mt-1 block text-[9px] font-bold uppercase tracking-widest text-emerald-200/80">JAM</span>
                        </div>
                        <span class="text-lg font-bold text-white/40">:</span>
                        <div class="min-w-[68px] rounded-xl bg-[#0D4A31] border border-white/15 px-4 py-2.5 text-center">
                            <span class="block font-serif-display text-2xl sm:text-3xl font-bold text-white leading-none" x-text="minutes">00</span>
                            <span class="mt-1 block text-[9px] font-bold uppercase tracking-widest text-emerald-200/80">MENIT</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2. Tiga Kartu Informasi Perjalanan --}}
            <div class="grid gap-5 md:grid-cols-3">
                {{-- Kartu 1: Titik Kumpul --}}
                <section class="rounded-[24px] border border-stone-200/90 bg-white p-5 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-stone-600">
                            <x-icon name="pin_drop" class="w-4 h-4 text-emerald-700" />
                            <span>TITIK KUMPUL</span>
                        </span>
                        <span class="rounded-full px-3 py-0.5 text-[10px] font-bold {{ $isTitikKumpulConfirmed ? 'bg-[#CFE5D5] text-[#063B25]' : 'bg-[#F3E2BA] text-[#8A5311]' }}">
                            {{ $isTitikKumpulConfirmed ? 'Terkonfirmasi' : 'Menunggu' }}
                        </span>
                    </div>

                    <div class="space-y-2.5 flex-1">
                        <div class="rounded-2xl bg-[#FBF9F3] border border-stone-200/60 px-4 py-3">
                            <span class="block text-[10px] text-stone-400 font-semibold">Lokasi Pertemuan</span>
                            <p class="mt-0.5 text-xs font-bold text-stone-900 leading-relaxed">
                                {{ $jadwal?->tempat_kumpul ?: 'Akan diinformasikan oleh admin ROZWIA' }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-[#FBF9F3] border border-stone-200/60 px-4 py-3">
                            <span class="block text-[10px] text-stone-400 font-semibold">Waktu Kumpul</span>
                            <p class="mt-0.5 text-xs font-bold text-stone-900">
                                {{ $jadwal?->tanggal_kumpul ? $jadwal->tanggal_kumpul->format('d/m/Y, H:i') . ' WIB' : 'Menunggu konfirmasi jadwal' }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- kartu tiket pesawat --}}
                <section class="rounded-[24px] border border-stone-200/90 bg-white p-5 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-stone-600">
                            <x-icon name="travel" class="w-4 h-4 text-sky-700" />
                            <span>TIKET PESAWAT</span>
                        </span>
                        <span class="rounded-full px-3 py-0.5 text-[10px] font-bold {{ $isFlightConfirmed ? 'bg-[#CFE5D5] text-[#063B25]' : 'bg-[#F3E2BA] text-[#8A5311]' }}">
                            {{ $isFlightConfirmed ? 'Terkonfirmasi' : 'Menunggu Jadwal' }}
                        </span>
                    </div>

                    <div class="flex-1 flex flex-col justify-center">
                        <div class="rounded-2xl bg-[#FBF9F3] border border-stone-200/60 px-4 py-4">
                            <span class="block text-[10px] text-stone-400 font-semibold">Maskapai Penerbangan</span>
                            <h3 class="mt-1 font-serif-display text-lg font-bold text-stone-900">{{ $maskapaiName }}</h3>
                        </div>
                    </div>
                </section>

                {{-- Kartu 3: Akomodasi Hotel --}}
                <section class="rounded-[24px] border border-stone-200/90 bg-white p-5 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-stone-600">
                            <x-icon name="night_shelter" class="w-4 h-4 text-amber-700" />
                            <span>AKOMODASI HOTEL</span>
                        </span>
                    </div>

                    <div class="space-y-2.5 flex-1">
                        @if($hotelList->isNotEmpty())
                            @foreach($hotelList->take(2) as $h)
                                <div class="rounded-2xl bg-[#FBF9F3] border border-stone-200/60 px-4 py-2.5">
                                    <span class="block text-[10px] text-stone-400 font-semibold">
                                        {{ $h->kota ? $h->kota : 'Hotel' }} (Bintang {{ $h->bintang ?? 5 }})
                                    </span>
                                    <p class="mt-0.5 text-xs font-bold text-stone-900 truncate">{{ $h->nama_hotel }}</p>
                                </div>
                            @endforeach
                        @else
                            <div class="rounded-2xl bg-[#FBF9F3] border border-stone-200/60 px-4 py-3 text-xs text-stone-500">
                                Menunggu konfirmasi hotel akomodasi
                            </div>
                        @endif

                        <p class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-stone-600 pt-1">
                            <x-icon name="hotel" class="w-3.5 h-3.5 text-amber-700" />
                            <span>{{ $booking ? "Kamar {$room}" : 'Tipe kamar: Belum ditentukan' }}</span>
                        </p>
                    </div>
                </section>
            </div>

            {{-- 3. Tabel Status Surat Kemenag & Visa Jamaah --}}
            <section class="rounded-[24px] border border-stone-200/90 bg-white p-6 shadow-xs space-y-5">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-serif-display text-xl font-bold text-[#0B3B24]">Status Surat Kemenag &amp; Visa Jamaah</h3>
                        <p class="mt-0.5 text-xs text-stone-500">
                            Pantau penerbitan Surat Rekomendasi Kemenag dan e-Visa untuk seluruh anggota rombongan Anda.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="inline-flex items-center rounded-full bg-stone-100 p-1 text-[11px] font-bold">
                            <button type="button"
                                    @click="filterStatus = 'semua'"
                                    :class="filterStatus === 'semua' ? 'bg-[#0B3B24] text-white shadow-2xs' : 'text-stone-600 hover:text-stone-900'"
                                    class="px-3 py-1 rounded-full transition cursor-pointer">
                                Semua ({{ $totalJamaahCount }})
                            </button>
                            <button type="button"
                                    @click="filterStatus = 'terbit'"
                                    :class="filterStatus === 'terbit' ? 'bg-[#0B3B24] text-white shadow-2xs' : 'text-stone-600 hover:text-stone-900'"
                                    class="px-3 py-1 rounded-full transition cursor-pointer">
                                Sudah Terbit ({{ $publishedCount }})
                            </button>
                            <button type="button"
                                    @click="filterStatus = 'diproses'"
                                    :class="filterStatus === 'diproses' ? 'bg-[#0B3B24] text-white shadow-2xs' : 'text-stone-600 hover:text-stone-900'"
                                    class="px-3 py-1 rounded-full transition cursor-pointer">
                                Diproses ({{ $processingCount }})
                            </button>
                        </div>

                        <div class="relative w-full sm:w-52">
                            <span class="absolute left-3 top-2.5 text-stone-400">
                                <x-icon name="search" class="w-4 h-4" />
                            </span>
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Cari nama atau paspor..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-full bg-[#FBF9F3] border border-stone-300 text-xs focus:outline-none focus:border-[#0B3B24]">
                        </div>
                    </div>
                </div>

                @if($jamaahList->isNotEmpty())
                    <div class="overflow-x-auto rounded-2xl border border-stone-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-stone-50 text-stone-600 border-b border-stone-200">
                                    <th class="px-4 py-3 font-bold text-center w-12">No</th>
                                    <th class="px-4 py-3 font-bold">Nama Jamaah</th>
                                    <th class="px-4 py-3 font-bold">Nomor Paspor</th>
                                    <th class="px-4 py-3 font-bold">Surat Kemenag</th>
                                    <th class="px-4 py-3 font-bold">Status Visa</th>
                                    <th class="px-4 py-3 font-bold">Nomor Visa</th>
                                    <th class="px-4 py-3 font-bold">Tanggal Terbit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 text-stone-800">
                                <template x-for="row in filteredRows" :key="row.no">
                                    <tr class="hover:bg-stone-50/70 transition">
                                        <td class="px-4 py-3.5 text-center font-semibold text-stone-500" x-text="row.no"></td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-[#E6F4EA] text-[#0B3B24] font-bold text-[11px] flex items-center justify-center shrink-0" x-text="row.initials"></div>
                                                <div>
                                                    <strong class="block text-stone-900 font-bold" x-text="row.nama"></strong>
                                                    <span class="text-[10px] text-stone-400" x-text="row.sub"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 font-mono text-stone-700" x-text="row.paspor"></td>
                                        <td class="px-4 py-3.5">
                                            <span :class="{
                                                'bg-emerald-50 text-emerald-800 border-emerald-200': row.kemenag === 'Terbit',
                                                'bg-yellow-100 text-yellow-800 border-yellow-300': row.kemenag === 'Diproses',
                                                'bg-rose-50 text-rose-700 border-rose-200': row.kemenag === 'Ditolak',
                                                'bg-stone-100 text-stone-600 border-stone-200': row.kemenag !== 'Terbit' && row.kemenag !== 'Diproses' && row.kemenag !== 'Ditolak'
                                            }" class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-bold whitespace-nowrap">
                                                <span :class="{
                                                    'bg-emerald-600': row.kemenag === 'Terbit',
                                                    'bg-yellow-500': row.kemenag === 'Diproses',
                                                    'bg-rose-600': row.kemenag === 'Ditolak',
                                                    'bg-stone-400': row.kemenag !== 'Terbit' && row.kemenag !== 'Diproses' && row.kemenag !== 'Ditolak'
                                                }" class="w-1.5 h-1.5 rounded-full"></span>
                                                <span x-text="row.kemenag === 'Terbit' ? 'Sudah Terbit' : (row.kemenag === 'Diproses' ? 'Sedang Diproses' : row.kemenag)"></span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span :class="{
                                                'bg-emerald-50 text-emerald-800 border-emerald-200': row.visa === 'Terbit',
                                                'bg-yellow-100 text-yellow-800 border-yellow-300': row.visa === 'Diproses',
                                                'bg-rose-50 text-rose-700 border-rose-200': row.visa === 'Ditolak',
                                                'bg-stone-100 text-stone-600 border-stone-200': row.visa !== 'Terbit' && row.visa !== 'Diproses' && row.visa !== 'Ditolak'
                                            }" class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-bold whitespace-nowrap">
                                                <span :class="{
                                                    'bg-emerald-600': row.visa === 'Terbit',
                                                    'bg-yellow-500': row.visa === 'Diproses',
                                                    'bg-rose-600': row.visa === 'Ditolak',
                                                    'bg-stone-400': row.visa !== 'Terbit' && row.visa !== 'Diproses' && row.visa !== 'Ditolak'
                                                }" class="w-1.5 h-1.5 rounded-full"></span>
                                                <span x-text="row.visa === 'Terbit' ? 'Sudah Terbit' : (row.visa === 'Diproses' ? 'Sedang Diproses' : row.visa)"></span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span x-show="row.nomor_visa" class="font-mono font-semibold text-stone-800" x-text="row.nomor_visa"></span>
                                            <span x-show="!row.nomor_visa" class="italic text-stone-400">Menunggu Penerbitan</span>
                                        </td>
                                        <td class="px-4 py-3.5 font-medium text-stone-700 whitespace-nowrap" x-text="row.tanggal_terbit"></td>
                                    </tr>
                                </template>
                                <tr x-show="filteredRows.length === 0">
                                    <td colspan="7" class="px-4 py-6 text-center text-stone-400">
                                        Tidak ada data jamaah yang cocok dengan filter pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-stone-500 pt-1">
                        <span>Menampilkan <strong class="text-stone-700" x-text="filteredRows.length"></strong> dari <strong class="text-stone-700">{{ $totalJamaahCount }}</strong> jamaah terdaftar</span>
                        <span>
                            Pembaruan terakhir: {{ $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->timezone('Asia/Jakarta')->format('d/m/Y, H:i') . ' WIB' : '-' }}
                        </span>
                    </div>
                @else
                    <div class="rounded-2xl bg-[#FBF9F3] border border-stone-200/70 p-4 text-xs text-stone-600">
                        Data jamaah akan tampil di sini setelah booking dan data jamaah dibuat.
                    </div>
                @endif
            </section>

        </div>
    </main>
</div>
@endsection

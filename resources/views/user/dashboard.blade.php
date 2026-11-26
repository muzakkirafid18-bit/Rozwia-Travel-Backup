@extends('layouts.dashboard')

@section('title', 'Beranda Jamaah - ROZWIA Islamic Travel')

@section('content')
@php
    $jamaahCount = max(1, $booking?->jamaahDetails?->count() ?? ($booking?->jumlah_jamaah ?? 1));
    $allDokumens = $booking?->jamaahDetails?->flatMap(fn($j) => $j->dokumens) ?? ($dokumens ?? collect());
    $uploadedDocs = $allDokumens->count();
    $verifiedDocs = $allDokumens->where('status', 'Diverifikasi')->count();
    $totalRequiredDocs = $jamaahCount * 5;
    $remainingDocs = max(0, $totalRequiredDocs - $uploadedDocs);
    $docProgressPercent = $totalRequiredDocs > 0 ? min(100, round(($uploadedDocs / $totalRequiredDocs) * 100, 1)) : 0;

    $docsComplete = $uploadedDocs >= $totalRequiredDocs && $verifiedDocs >= $totalRequiredDocs;
    $isLunas = $totalBiaya > 0 && $sisaTagihan <= 0;
    $dpVerified = in_array($statusDp, ['DP Terverifikasi Admin', 'Pembayaran Lunas']) || $totalDibayar > 0;

    $paket = $booking?->jadwal?->paket;
    $jadwal = $booking?->jadwal;
    $notifikasis = $user->notifikasis()->latest()->take(3)->get();
    $unreadNotifCount = $user->notifikasis()->where('is_read', false)->count();
    $room = $booking?->catatan ? str_replace('Tipe Kamar: ', '', $booking->catatan) : 'Belum dipilih';
    $jatuhTempo = $jadwal?->tanggal_keberangkatan
        ? $jadwal->tanggal_keberangkatan->copy()->subDays(14)->format('d/m/Y')
        : 'Menunggu jadwal';

    $isProfileIncomplete = $booking && $booking->jamaahDetails->contains(fn ($m) =>
        empty(trim((string) $m->nama_lengkap))
        || str_starts_with((string) $m->nama_lengkap, 'Anggota Jamaah ')
        || empty($m->nik)
        || empty($m->tempat_lahir)
        || empty($m->tanggal_lahir)
        || empty($m->jenis_kelamin)
    );

    if (!$dpVerified) {
        $nextTitle = 'Bayar DP & Unggah Bukti Pembayaran';
        $nextDesc  = 'Segera lakukan pembayaran DP agar admin dapat memverifikasi dan Anda bisa lanjut melengkapi dokumen.';
    } elseif (!$docsComplete) {
        if ($remainingDocs === 0) {
            $nextTitle = 'Menunggu Verifikasi Dokumen oleh Admin';
            $nextDesc  = "Seluruh {$uploadedDocs} berkas rombongan sudah diunggah ({$verifiedDocs} dari {$totalRequiredDocs} berkas sudah diverifikasi admin).";
        } else {
            $nextTitle = 'Unggah dokumen Persyaratan';
            $nextDesc  = 'Lengkapi dokumen agar admin dapat memeriksa dan booking Anda berlanjut ke pelunasan.';
        }
    } elseif (!$isLunas) {
        $nextTitle = 'Selesaikan Pelunasan Biaya';
        $nextDesc  = 'Dokumen persyaratan Anda sudah lengkap. Silakan selesaikan sisa tagihan sebelum jatuh tempo.';
    } else {
        $nextTitle = 'Persiapan Manasik & Keberangkatan';
        $nextDesc  = 'Pembayaran dan dokumen Anda sudah lengkap. Pantau jadwal manasik dan keberangkatan Anda di Status Perjalanan.';
    }

    $maskapaiNama = $jadwal?->maskapai?->nama_maskapai ?? 'Menunggu maskapai';
    $maskapaiLabel = $maskapaiNama;

    $statusBookingBadge = match($booking?->status_booking) {
        'Lunas', 'DP_Terverifikasi' => 'Pasti Berangkat',
        'Menunggu_DP' => 'Menunggu DP',
        default => $booking?->status_booking ? str_replace('_', ' ', $booking->status_booking) : 'Menunggu Booking',
    };

    $sapaan = \App\Helpers\RozwiaHelper::userSalutation($user, $booking?->jamaahDetails?->first());
@endphp

<div class="min-h-screen bg-[#EAE0D0] lg:grid lg:grid-cols-[290px_minmax(0,1fr)] xl:grid-cols-[320px_minmax(0,1fr)]">
    @include('user._sidebar', ['active' => 'beranda'])

    @if($isProfileIncomplete)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-[#063B25]/85 backdrop-blur-sm p-4 font-sans">
        <div class="w-full max-w-md rounded-[28px] bg-white p-7 shadow-2xl border border-stone-200 space-y-5 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-800">
                <x-icon name="account_circle" class="w-8 h-8" />
            </div>
            <div class="space-y-2">
                <h2 class="font-serif-display text-2xl font-bold text-[#063B25]">Lengkapi Profil Terlebih Dahulu</h2>
                <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                    Selamat datang di Portal Jamaah ROZWIA. Sebelum mengakses layanan dokumen dan perjalanan, {{ $sapaan }} wajib melengkapi data profil jamaah terlebih dahulu.
                </p>
            </div>
            <a href="{{ route('user.profile') }}"
               class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-[#063B25] hover:bg-emerald-900 py-3.5 px-6 text-xs sm:text-sm font-bold text-white shadow-md transition">
                <span>Lengkapi Profil Sekarang</span>
                <x-icon name="arrow_right" class="w-4 h-4" />
            </a>
        </div>
    </div>
    @endif

    <main class="min-w-0 p-5 sm:p-8 lg:p-10 xl:p-12">
        @include('user._pelunasan-popup', [
            'showPelunasanPopup' => $docsComplete && !$isLunas && !$isProfileIncomplete,
        ])
        <div class="mx-auto max-w-6xl space-y-6">
            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-900">{{ session('error') }}</div>
            @endif

            {{-- Header Sambutan & Lonceng Notifikasi --}}
            <div class="flex justify-between items-center gap-4"
                 x-data="{
                    unreadCount: {{ $unreadNotifCount }},
                    bukaNotifikasi() {
                        if (this.unreadCount > 0) {
                            this.unreadCount = 0;
                            fetch('{{ route('user.notifikasi.read') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            }).catch(() => {});
                        }
                        const target = document.getElementById('notifikasi');
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }
                 }">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold font-serif-display text-[#063B25]">
                        Assalamu’alaikum Warahmatullahi Wabarakatuh, {{ $sapaan }} {{ $user->name }}
                    </h1>
                    <p class="text-xs sm:text-sm text-stone-600 mt-1">
                        Selamat datang di Portal Jamaah ROZWIA Islamic Travel. Semoga Allah senantiasa memudahkan langkah dan persiapan ibadah {{ $sapaan }} beserta keluarga.
                    </p>
                    @if($paket)
                        <div class="mt-2 inline-flex flex-wrap items-center gap-2 rounded-full bg-[#063B25]/10 border border-[#063B25]/20 px-3.5 py-1 text-xs font-semibold text-[#063B25] font-sans">
                            <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA" class="h-4 w-4 rounded-full bg-[#063B25] p-0.5 object-contain">
                            <span>Paket Dipesan:</span>
                            <strong>{{ $paket->nama_paket }}</strong>
                            @if($booking?->kode_booking)
                                <span class="text-stone-500 font-mono">({{ $booking->kode_booking }})</span>
                            @endif
                        </div>
                    @endif
                    @if($booking)
                        <div class="mt-2.5 flex flex-wrap items-center gap-2">
                            <a href="{{ route('user.kuitansi') }}" target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-full bg-[#B78616] hover:bg-[#9c7110] text-white px-4 py-1.5 text-xs font-bold shadow-xs transition font-sans">
                                <x-icon name="receipt_long" class="w-4 h-4" />
                                <span>Cetak Kuitansi / Invoice</span>
                            </a>
                        </div>
                    @endif
                </div>
                <button type="button"
                        @click="bukaNotifikasi()"
                        class="w-12 h-12 rounded-2xl bg-white border border-stone-200 shadow-xs flex items-center justify-center text-[#B78616] text-xl cursor-pointer hover:bg-stone-50 transition relative shrink-0"
                        title="Lihat Notifikasi">
                    <x-icon name="notifications" class="w-5 h-5" />
                    <span x-show="unreadCount > 0"
                          x-transition
                          class="absolute top-2.5 right-2.5 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </button>
            </div>

            {{-- 1. Card: Langkah Anda Berikutnya --}}
            <div class="bg-[#063B25] text-white rounded-[32px] p-7 sm:p-8 shadow-sm space-y-4">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-[#E7C878] uppercase tracking-widest">Langkah Anda Berikutnya</span>
                    <h3 class="text-xl sm:text-2xl font-bold font-serif-display">{{ $nextTitle }}</h3>
                    <p class="text-xs sm:text-sm text-emerald-100/85 max-w-2xl">
                        {{ $nextDesc }}
                    </p>
                </div>

                {{-- Progress Bar --}}
                <div class="space-y-2 pt-2">
                    <div class="flex justify-between items-center text-xs font-semibold">
                        <span class="text-emerald-200">Dokumen terkumpul</span>
                        <span class="text-[#E7C878]">[{{ $uploadedDocs }} dari {{ $totalRequiredDocs }} terunggah · {{ $verifiedDocs }}/{{ $totalRequiredDocs }} terverifikasi]</span>
                    </div>
                    <div class="w-full bg-[#D6E2DC] h-3 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 bg-[#EA8A00]" style="width: {{ $docProgressPercent }}%; background-color: #EA8A00;"></div>
                    </div>
                </div>
            </div>

            {{-- 2. Card: Tahap Booking (4 Langkah) --}}
            <div class="bg-white rounded-[32px] p-6 sm:p-7 border border-stone-200/80 shadow-sm space-y-3">
                <div class="flex flex-wrap justify-between items-center gap-2">
                    <h3 class="text-sm font-bold font-serif-display text-stone-900">Tahap Booking</h3>
                    <span class="text-[11px] text-stone-500">Ikuti 4 langkah mudah menuju keberangkatan tanah suci</span>
                </div>

                <div class="bg-[#E5E3DF] rounded-2xl p-3.5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    {{-- Tahap 1 --}}
                    <div class="rounded-xl p-4 space-y-1.5 border {{ $dpVerified ? 'bg-[#CFE5D5] border-[#84C39A]' : 'bg-[#F3E2BA] border-[#D9A74A]' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full {{ $dpVerified ? 'bg-[#063B25]' : 'bg-[#D9822B]' }} text-white flex items-center justify-center text-xs font-bold">
                                {{ $dpVerified ? '✓' : '1' }}
                            </span>
                            <strong class="text-xs font-bold {{ $dpVerified ? 'text-[#063B25]' : 'text-[#8A5311]' }}">Tahap 1</strong>
                        </div>
                        <p class="text-xs font-bold text-stone-900">Bayar DP {{ $paket?->kategori === 'Haji' ? 'Haji' : 'Umroh' }}</p>
                        <p class="text-[11px] {{ $dpVerified ? 'text-[#1E6B43]' : 'text-[#9A5B13]' }} font-medium">
                            {{ $dpVerified ? 'Terverifikasi (' . \App\Helpers\RozwiaHelper::formatRupiah($totalDibayar) . ')' : $statusDp }}
                        </p>
                    </div>

                    {{-- Tahap 2 --}}
                    @php
                        $step2State = $docsComplete ? 'done' : ($dpVerified ? 'active' : 'pending');
                    @endphp
                    <div class="rounded-xl p-4 space-y-1.5 border {{ $step2State === 'done' ? 'bg-[#CFE5D5] border-[#84C39A]' : ($step2State === 'active' ? 'bg-[#F3E2BA] border-[#D9A74A]' : 'bg-[#D9D9D9] border-stone-300') }}">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $step2State === 'done' ? 'bg-[#063B25] text-white' : ($step2State === 'active' ? 'bg-[#D9822B] text-white' : 'bg-[#8C8C8C] text-white') }}">
                                {{ $step2State === 'done' ? '✓' : '2' }}
                            </span>
                            <strong class="text-xs font-bold {{ $step2State === 'done' ? 'text-[#063B25]' : ($step2State === 'active' ? 'text-[#8A5311]' : 'text-stone-600') }}">
                                {{ $step2State === 'done' ? 'Tahap 2' : ($step2State === 'active' ? 'Sedang Berjalan' : 'Menunggu') }}
                            </strong>
                        </div>
                        <p class="text-xs font-bold text-stone-900">Unggah Dokumen</p>
                        <p class="text-[11px] {{ $step2State === 'done' ? 'text-[#1E6B43]' : ($step2State === 'active' ? 'text-[#9A5B13]' : 'text-stone-500') }} font-medium">
                            {{ $docsComplete ? 'Lengkap & Terverifikasi' : ($remainingDocs === 0 ? 'Menunggu Verifikasi (' . $verifiedDocs . '/' . $totalRequiredDocs . ')' : $remainingDocs . ' Berkas Belum Masuk') }}
                        </p>
                    </div>

                    {{-- Tahap 3 --}}
                    @php
                        $step3State = $isLunas ? 'done' : ($docsComplete ? 'active' : 'pending');
                    @endphp
                    <div class="rounded-xl p-4 space-y-1.5 border {{ $step3State === 'done' ? 'bg-[#CFE5D5] border-[#84C39A]' : ($step3State === 'active' ? 'bg-[#F3E2BA] border-[#D9A74A]' : 'bg-[#D9D9D9] border-stone-300') }}">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $step3State === 'done' ? 'bg-[#063B25] text-white' : ($step3State === 'active' ? 'bg-[#D9822B] text-white' : 'bg-[#8C8C8C] text-white') }}">
                                {{ $step3State === 'done' ? '✓' : '3' }}
                            </span>
                            <strong class="text-xs font-bold {{ $step3State === 'done' ? 'text-[#063B25]' : ($step3State === 'active' ? 'text-[#8A5311]' : 'text-stone-600') }}">
                                {{ $step3State === 'done' ? 'Tahap 3' : ($step3State === 'active' ? 'Sedang Berjalan' : 'Menunggu') }}
                            </strong>
                        </div>
                        <p class="text-xs font-bold text-stone-900">Pelunasan Biaya</p>
                        <p class="text-[11px] {{ $step3State === 'done' ? 'text-[#1E6B43]' : ($step3State === 'active' ? 'text-[#9A5B13]' : 'text-stone-500') }} font-medium">
                            {{ $isLunas ? 'Lunas Terverifikasi' : 'Sisa ' . \App\Helpers\RozwiaHelper::formatRupiah(max(0, $sisaTagihan)) }}
                        </p>
                    </div>

                    {{-- Tahap 4 --}}
                    @php
                        $step4Active = $docsComplete && $isLunas;
                    @endphp
                    <div class="rounded-xl p-4 space-y-1.5 border {{ $step4Active ? 'bg-[#F3E2BA] border-[#D9A74A]' : 'bg-[#D9D9D9] border-stone-300' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full {{ $step4Active ? 'bg-[#D9822B]' : 'bg-[#3F3F46]' }} text-white flex items-center justify-center text-xs font-bold">4</span>
                            <strong class="text-xs font-bold {{ $step4Active ? 'text-[#8A5311]' : 'text-stone-700' }}">Terakhir</strong>
                        </div>
                        <p class="text-xs font-bold text-stone-900">Manasik & Berangkat</p>
                        <p class="text-[11px] text-stone-600 font-medium">
                            Jadwal {{ $jadwal?->tanggal_keberangkatan?->format('d/m/Y') ?? 'Menunggu jadwal' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- 3. Dua Kartu Sejajar: Paket Saya & Keuangan Booking --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Kartu Paket Saya --}}
                <div class="bg-white rounded-[32px] p-6 border border-stone-200 shadow-sm space-y-5 flex flex-col justify-between">
                    <div class="space-y-5">
                        <div class="flex justify-between items-center border-b border-stone-100 pb-3">
                            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">PAKET SAYA</span>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-stone-900 font-serif-display">{{ $paket?->nama_paket ?? 'Belum Ada Paket Terdaftar' }}</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-1 text-xs">
                            <div>
                                <span class="text-stone-400 block text-[11px] mb-1">Tanggal Keberangkatan</span>
                                <strong class="text-stone-800 font-serif-display inline-flex items-center gap-1.5 text-sm">
                                    <x-icon name="calendar_month" class="w-4 h-4 text-stone-700" />
                                    <span>{{ $jadwal?->tanggal_keberangkatan?->format('d/m/Y') ?? 'Menunggu jadwal' }}</span>
                                </strong>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-[11px] mb-1">Maskapai &amp; Rute</span>
                                <strong class="text-stone-800 font-serif-display inline-flex items-center gap-1.5 text-sm">
                                    <x-icon name="travel" class="w-4 h-4 text-stone-700" />
                                    <span>{{ $maskapaiLabel }}</span>
                                </strong>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-[11px] mb-1">Tipe Kamar Hotel</span>
                                <strong class="text-stone-800 font-serif-display inline-flex items-center gap-1.5 text-sm">
                                    <x-icon name="night_shelter" class="w-4 h-4 text-stone-700" />
                                    <span>{{ $room }}</span>
                                </strong>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-[11px] mb-1">Jumlah Jamaah Terdaftar</span>
                                <strong class="text-stone-800 font-serif-display inline-flex items-center gap-1.5 text-sm">
                                    <x-icon name="group" class="w-4 h-4 text-stone-700" />
                                    @if($booking)
                                        <span>{{ $booking->jumlah_jamaah }} Orang ({{ $booking->jamaahDetails?->first()?->nama_lengkap ?? $user->name }})</span>
                                    @else
                                        <span>Belum ada pemesanan</span>
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu Keuangan Booking --}}
                <div class="bg-white rounded-[32px] p-6 border border-stone-200 shadow-sm flex flex-col justify-between space-y-5">
                    <div class="flex justify-between items-center border-b border-stone-100 pb-3">
                        <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">KEUANGAN BOOKING</span>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs text-stone-500">Status Pembayaran</span>
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="bg-emerald-50/70 p-3.5 rounded-2xl border border-emerald-200">
                                <span class="text-[10px] font-bold text-emerald-800 block">Sudah Dibayar (DP)</span>
                                <strong class="text-base font-bold text-emerald-900 font-serif-display">{{ \App\Helpers\RozwiaHelper::formatRupiah($totalDibayar) }}</strong>
                                <span class="text-[10px] text-emerald-700 block mt-0.5">{{ $statusDp }}</span>
                            </div>
                            <div class="bg-amber-50 p-3.5 rounded-2xl border border-amber-300 relative">
                                <span class="text-[10px] font-bold text-amber-900 block">Sisa Tagihan (Kurang)</span>
                                <strong class="text-base font-bold text-amber-950 font-serif-display">{{ \App\Helpers\RozwiaHelper::formatRupiah(max(0, $sisaTagihan)) }}</strong>
                                <span class="text-[10px] text-amber-800 block mt-0.5">Jatuh tempo: {{ $jatuhTempo }}</span>
                            </div>
                        </div>
                    </div>
                    @if(!$booking)
                        <div class="w-full bg-stone-100 text-stone-600 font-semibold py-2.5 px-3 rounded-xl text-xs text-center flex items-center justify-center gap-1.5">
                            <x-icon name="info" class="w-4 h-4 text-stone-500" />
                            <span>Belum ada tagihan (Silakan pilih paket)</span>
                        </div>
                    @elseif($sisaTagihan > 0)
                        <a href="{{ route('user.formulir.dp') }}" class="w-full bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold py-2.5 rounded-xl text-xs transition cursor-pointer shadow-xs text-center block">
                            Lanjutkan Pelunasan
                        </a>
                    @else
                        <div class="w-full bg-emerald-100 text-emerald-800 font-bold py-2.5 rounded-xl text-xs text-center flex items-center justify-center gap-1.5">
                            <x-icon name="check_circle" class="w-4 h-4 text-emerald-700" />
                            <span>Pembayaran Lunas</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 4. Bagian Bawah: Notifikasi Baru & Butuh Bantuan --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Kotak Notifikasi Baru (Col 7) --}}
                <div id="notifikasi" class="lg:col-span-7 bg-white rounded-[32px] p-6 sm:p-7 border border-stone-200 shadow-sm space-y-4">
                    <h3 class="text-base font-bold font-serif-display text-stone-900 pb-2 border-b border-stone-100">Notifikasi Baru</h3>

                    <div class="space-y-3.5 text-xs">
                        @forelse($notifikasis as $notif)
                            @php
                                $dotColor = match($notif->notification_type) {
                                    'Pembayaran' => 'bg-emerald-600',
                                    'Dokumen' => 'bg-rose-600',
                                    default => 'bg-amber-500',
                                };
                            @endphp
                            <div class="flex items-start gap-3 {{ !$loop->last ? 'pb-3 border-b border-stone-100' : '' }}">
                                <span class="w-2.5 h-2.5 rounded-full {{ $dotColor }} mt-1 shrink-0"></span>
                                <div>
                                    <p class="text-stone-800 font-semibold">{{ $notif->title }}</p>
                                    <p class="text-stone-600 mt-0.5">{{ $notif->message }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-start gap-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-1 shrink-0"></span>
                                <p class="text-stone-600 font-medium">Belum ada notifikasi baru. Pantau terus perkembangan booking Anda di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Kotak Butuh Bantuan? (Col 5) --}}
                <div class="lg:col-span-5 bg-[#0B3B24] text-white rounded-[32px] p-6 sm:p-7 shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <h3 class="text-base font-bold font-serif-display">Butuh Bantuan?</h3>
                        <p class="text-xs text-emerald-100/80 leading-relaxed">
                            Tim ROZWIA siap membantu seputar pembayaran, dokumen, dan keberangkatan.
                        </p>
                    </div>

                    <div class="space-y-2.5 pt-2">
                        <a href="https://wa.me/6285122978237" target="_blank" class="w-full bg-[#25D366] hover:bg-[#20ba59] text-stone-900 font-bold py-3 rounded-2xl flex items-center justify-center gap-2 transition shadow-sm text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 256 258">
                                <path d="M0 0h256v258H0z" fill="none" />
                                <defs>
                                    <linearGradient id="waGrad1" x1="50%" x2="50%" y1="100%" y2="0%">
                                        <stop offset="0%" stop-color="#1faf38" />
                                        <stop offset="100%" stop-color="#60d669" />
                                    </linearGradient>
                                    <linearGradient id="waGrad2" x1="50%" x2="50%" y1="100%" y2="0%">
                                        <stop offset="0%" stop-color="#f9f9f9" />
                                        <stop offset="100%" stop-color="#fff" />
                                    </linearGradient>
                                </defs>
                                <path fill="url(#waGrad1)" d="M5.463 127.456c-.006 21.677 5.658 42.843 16.428 61.499L4.433 252.697l65.232-17.104a123 123 0 0 0 58.8 14.97h.054c67.815 0 123.018-55.183 123.047-123.01c.013-32.867-12.775-63.773-36.009-87.025c-23.23-23.25-54.125-36.061-87.043-36.076c-67.823 0-123.022 55.18-123.05 123.004" />
                                <path fill="url(#waGrad2)" d="M1.07 127.416c-.007 22.457 5.86 44.38 17.014 63.704L0 257.147l67.571-17.717c18.618 10.151 39.58 15.503 60.91 15.511h.055c70.248 0 127.434-57.168 127.464-127.423c.012-34.048-13.236-66.065-37.3-90.15C194.633 13.286 162.633.014 128.536 0C58.276 0 1.099 57.16 1.071 127.416m40.24 60.376l-2.523-4.005c-10.606-16.864-16.204-36.352-16.196-56.363C22.614 69.029 70.138 21.52 128.576 21.52c28.3.012 54.896 11.044 74.9 31.06c20.003 20.018 31.01 46.628 31.003 74.93c-.026 58.395-47.551 105.91-105.943 105.91h-.042c-19.013-.01-37.66-5.116-53.922-14.765l-3.87-2.295l-40.098 10.513z" />
                                <path fill="#fff" d="M96.678 74.148c-2.386-5.303-4.897-5.41-7.166-5.503c-1.858-.08-3.982-.074-6.104-.074c-2.124 0-5.575.799-8.492 3.984c-2.92 3.188-11.148 10.892-11.148 26.561s11.413 30.813 13.004 32.94c1.593 2.123 22.033 35.307 54.405 48.073c26.904 10.609 32.379 8.499 38.218 7.967c5.84-.53 18.844-7.702 21.497-15.139c2.655-7.436 2.655-13.81 1.859-15.142c-.796-1.327-2.92-2.124-6.105-3.716s-18.844-9.298-21.763-10.361c-2.92-1.062-5.043-1.592-7.167 1.597c-2.124 3.184-8.223 10.356-10.082 12.48c-1.857 2.129-3.716 2.394-6.9.801c-3.187-1.598-13.444-4.957-25.613-15.806c-9.468-8.442-15.86-18.867-17.718-22.056c-1.858-3.184-.199-4.91 1.398-6.497c1.431-1.427 3.186-3.719 4.78-5.578c1.588-1.86 2.118-3.187 3.18-5.311c1.063-2.126.531-3.986-.264-5.579c-.798-1.593-6.987-17.343-9.819-23.64" />
                            </svg>
                            Chat Via WhatsApp
                        </a>
                        <a href="{{ route('faq') }}" class="w-full bg-transparent hover:bg-white/10 text-white border border-white/20 font-bold py-3 rounded-2xl flex items-center justify-center gap-2 transition text-xs">
                            Lihat Pertanyaan Umum
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

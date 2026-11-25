@extends('layouts.admin')

@section('title', 'Beranda - Admin ROZWIA')

@section('content')
<div class="space-y-8 font-serif">
    
    @if(session('success'))
    <div class="p-4 bg-emerald-100 border border-emerald-300 rounded-2xl text-emerald-900 text-sm flex items-center gap-3 shadow-sm font-sans">
        <x-icon name="check_circle" class="w-5 h-5 text-emerald-700" />
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-100 border border-rose-300 rounded-2xl text-rose-900 text-sm flex items-center gap-3 shadow-sm font-sans">
        <x-icon name="warning" class="w-5 h-5 text-rose-700" />
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- banner sambutan admin -->
    <div class="bg-[#063B25] text-white rounded-[24px] p-6 sm:p-8 shadow-sm flex flex-col md:flex-row items-start md:items-center gap-6">
        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-[#042D1C] border border-white/10 flex flex-col items-center justify-center p-3 shrink-0">
            <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA Islamic Travel" class="h-14 w-auto object-contain">
        </div>
        <div class="space-y-2 max-w-3xl">
            <h1 class="font-serif-display text-2xl sm:text-4xl font-bold text-white tracking-tight flex items-center gap-2.5">
                <span>Selamat Datang di Dashboard Admin</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 128 128" aria-hidden="true" class="shrink-0">
                    <path d="M0 0h128v128H0z" fill="none" />
                    <path fill="#ffca28" d="M93.3 60c.2.3.7.1.7-.2c.6-5.4 2.2-20.3 12.8-23.5c3.4-1 6.8 1.4 7.2 4.5c.7 5-5 17.9-4.5 29.7c.1 1.9 3.3 22-5.2 33.9s-28.7 24-48.8 5.6c-10.4-9.5-10.4-13.3-23.3-26.6c-2.6-2.6-13-14-15.8-17.5c-3.7-4.7 2.2-10.9 6.7-7.7c2.1 1.5 20.7 17.1 21.4 17.8c1.4 1.2 3.1-.5 2.1-1.7c-11.4-15-22.4-28.5-25.4-33.7s4.2-10.8 8.4-6.3c2.9 3 24.4 28.1 25.4 29.2s2.7-.3 2.1-1.7c-.7-1.3-18.1-31.6-21.2-38.5c-2.7-6.1 6.3-11.8 10.5-5.5c3.4 5 22.4 36.6 23.1 37.7c.9 1.6 2.9.6 2.1-1.2C71 53 59.3 21 58.4 17.7c-1.6-5.8 6.7-10.6 10.6-4c5.3 9.1 15.8 36.9 24.3 46.3" />
                    <path fill="#b0bec5" d="M10.6 81.3c3.1 9.2 7.8 14.9 15.5 20.1c1.4 1 .6 3-1 2.3c-7.2-2.9-15.2-9.3-17.1-22c-.3-1.7 2.1-2 2.6-.4m6-5.5c3.1 9.2 7.8 14.9 15.5 20.1c1.4 1 .6 3-1 2.3c-7.2-2.9-15.2-9.3-17.1-22c-.2-1.7 2.1-2 2.6-.4m76.9-41.2c-2.4-9.4-6.8-15.4-14.1-21c-1.4-1-.4-3 1.2-2.3c7 3.3 14.6 10.3 15.6 23.1c.1 1.7-2.3 1.9-2.7.2m6.5-4.8c-2.4-9.4-6.8-15.4-14.1-21c-1.4-1-.4-3 1.2-2.3c7 3.3 14.6 10.3 15.6 23.1c.1 1.6-2.2 1.8-2.7.2" />
                </svg>
            </h1>
            <p class="text-emerald-100/85 text-xs sm:text-sm leading-relaxed font-sans">
                Kelola seluruh proses perjalanan jamaah mulai dari pemantauan pendaftaran, verifikasi dokumen persyaratan, validasi bukti pembayaran, hingga pembaruan status jamaah. Pantau kesiapan paspor, setoran melalui rekening resmi perusahaan, serta penerbitan e-Visa Saudi dalam satu dashboard terpadu.
            </p>
        </div>
    </div>

    <!-- 3 kartu ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 font-sans">
        {{-- KPI 1: Antrean Aktivasi Akun --}}
        <div class="bg-[#FBF9F3] rounded-[20px] border border-stone-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-xs font-medium text-stone-500">Antrean Aktivasi Akun</span>
                    <h3 class="mt-2 font-serif-display text-3xl font-bold text-stone-900">{{ $antreanAktivasiAkun }} Akun</h3>
                </div>
                <span class="w-12 h-12 rounded-full bg-[#CFE5D5] text-[#063B25] flex items-center justify-center shrink-0">
                    <x-icon name="person" class="w-6 h-6" />
                </span>
            </div>
            <div class="pt-3 border-t border-stone-200/80 flex items-center justify-between text-xs">
                <span class="text-stone-600">Buka Halaman Aktivasi Akun</span>
                <a href="{{ route('admin.aktivasi', ['status' => 'inactive']) }}" class="font-bold text-[#063B25] hover:underline inline-flex items-center gap-1 shrink-0">
                    <span>Kelola Aktivasi</span>
                    <x-icon name="north_east" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>

        {{-- KPI 2: Antrean Verifikasi Berkas --}}
        <div class="bg-[#FBF9F3] rounded-[20px] border border-stone-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-xs font-medium text-stone-500">Antrean Verifikasi Berkas</span>
                    <h3 class="mt-2 font-serif-display text-3xl font-bold text-stone-900">{{ $antreanAkunDokumen ?? 0 }} Akun</h3>
                </div>
                <span class="w-12 h-12 rounded-full bg-[#EFE7D6] text-[#B78616] flex items-center justify-center shrink-0">
                    <x-icon name="description" class="w-6 h-6" />
                </span>
            </div>
            <div class="pt-3 border-t border-stone-200/80 flex items-center justify-between text-xs">
                <span class="text-stone-600">Butuh pemeriksaan lebih lanjut</span>
                <a href="{{ route('admin.dokumen') }}" class="font-bold text-[#063B25] hover:underline inline-flex items-center gap-1 shrink-0">
                    <span>Verifikasi</span>
                    <x-icon name="north_east" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>

        {{-- KPI 3: Total Jamaah Terdaftar --}}
        <div class="bg-[#FBF9F3] rounded-[20px] border border-stone-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-xs font-medium text-stone-500">Total Jamaah Terdaftar</span>
                    <h3 class="mt-2 font-serif-display text-3xl font-bold text-stone-900">{{ $totalJamaah }} Jamaah</h3>
                </div>
                <span class="w-12 h-12 rounded-full bg-[#EFE7D6] text-[#8A5311] flex items-center justify-center shrink-0">
                    <x-icon name="group" class="w-6 h-6" />
                </span>
            </div>
            <div class="pt-3 border-t border-stone-200/80 flex items-center justify-between text-xs">
                <span class="text-stone-600">Lihat Daftar Jamaah ({{ $jamaahAktif }} booking aktif)</span>
                <a href="{{ route('admin.aktivasi') }}" class="font-bold text-[#063B25] hover:underline inline-flex items-center gap-1 shrink-0">
                    <span>Kelola</span>
                    <x-icon name="north_east" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    </div>

    <!-- 3 kartu analitik -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 font-sans items-stretch">
        {{-- Kiri: Grafik Pendaftaran Jamaah 6 Bulan Terakhir --}}
        <div class="lg:col-span-6 bg-[#FBF9F3] rounded-[20px] border border-stone-200/80 p-6 shadow-xs flex flex-col justify-between space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full bg-[#EFE7D6] text-[#8A5311] flex items-center justify-center shrink-0">
                        <x-icon name="groups" class="w-5 h-5" />
                    </span>
                    <div>
                        <h3 class="font-serif-display text-base font-bold text-stone-900 leading-tight">Jumlah Pendaftaran Jamaah</h3>
                        <p class="text-[11px] text-stone-500">6 bulan terakhir (total pendaftar aktif maupun non-aktif)</p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-4 rounded-full bg-[#EFE7D6]/70 border border-stone-200/80 px-3.5 py-1.5 text-[11px] font-semibold text-stone-800">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#063B25]"></span>
                        Umroh
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#5E8B73]"></span>
                        Haji
                    </span>
                </div>
            </div>

            <div class="pt-2">
                <div class="relative h-48 flex items-end justify-between gap-3 border-b border-stone-300 pb-2 px-3">
                    <div class="pointer-events-none absolute inset-x-0 top-2 bottom-2 flex flex-col justify-between">
                        <div class="border-b border-dashed border-stone-300/60 w-full"></div>
                        <div class="border-b border-dashed border-stone-300/60 w-full"></div>
                        <div class="border-b border-dashed border-stone-300/60 w-full"></div>
                    </div>
                    @foreach($chartMonths as $m)
                        @php
                            $umrohPct = $chartMax > 0 ? round(($m['umroh'] / $chartMax) * 100) : 0;
                            $hajiPct = $chartMax > 0 ? round(($m['haji'] / $chartMax) * 100) : 0;
                        @endphp
                        <div class="relative z-10 flex-1 flex flex-col items-center justify-end h-full gap-1">
                            <div class="w-full flex items-end justify-center gap-1.5 h-full">
                                <div class="flex flex-col items-center justify-end h-full w-5 sm:w-6" title="Umroh: {{ $m['umroh'] }}">
                                    <span class="text-[10px] font-bold text-stone-700 mb-1 tabular-nums">{{ $m['umroh'] }}</span>
                                    <div class="w-full rounded-t-md bg-[#063B25] transition-all"
                                         style="height: {{ $m['umroh'] > 0 ? max(8, $umrohPct) : 3 }}%;"></div>
                                </div>
                                <div class="flex flex-col items-center justify-end h-full w-5 sm:w-6" title="Haji: {{ $m['haji'] }}">
                                    <span class="text-[10px] font-bold text-stone-700 mb-1 tabular-nums">{{ $m['haji'] }}</span>
                                    <div class="w-full rounded-t-md bg-[#5E8B73] transition-all"
                                         style="height: {{ $m['haji'] > 0 ? max(8, $hajiPct) : 3 }}%;"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between gap-3 px-3 pt-2.5">
                    @foreach($chartMonths as $m)
                        <span class="flex-1 text-center text-[10px] font-bold text-stone-600 truncate">{{ $m['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tengah: Jumlah Berkas Terkirim --}}
        <div class="lg:col-span-3 bg-[#FBF9F3] rounded-[20px] border border-stone-200/80 p-6 shadow-xs flex flex-col justify-between space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-full bg-[#EFE7D6] text-[#B78616] flex items-center justify-center shrink-0">
                    <x-icon name="description" class="w-5 h-5" />
                </span>
                <h3 class="font-serif-display text-base font-bold text-stone-900 leading-tight">Jumlah Berkas Terkirim</h3>
            </div>

            <div class="space-y-3 my-auto">
                <div class="rounded-2xl bg-[#EFE7D6]/70 border border-stone-200/60 px-4 py-3 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 font-medium text-stone-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#063B25]"></span>
                        Berhasil Diverifikasi
                    </span>
                    <strong class="text-stone-900 tabular-nums">{{ $berkasVerified }} Berkas</strong>
                </div>

                <div class="rounded-2xl bg-[#EFE7D6]/70 border border-stone-200/60 px-4 py-3 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 font-medium text-stone-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                        Butuh Perbaikan
                    </span>
                    <strong class="text-stone-900 tabular-nums">{{ $berkasRevisi }} Berkas</strong>
                </div>

                <div class="rounded-2xl bg-[#EFE7D6]/70 border border-stone-200/60 px-4 py-3 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 font-medium text-stone-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#EA8A00]"></span>
                        Menunggu Diverifikasi
                    </span>
                    <strong class="text-stone-900 tabular-nums">{{ $pendingDokumen }} Berkas</strong>
                </div>
            </div>
        </div>

        {{-- Kanan: Jumlah Akun Berdasarkan Status --}}
        <div class="lg:col-span-3 bg-[#FBF9F3] rounded-[20px] border border-stone-200/80 p-6 shadow-xs flex flex-col justify-between space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-full bg-[#CFE5D5] text-[#063B25] flex items-center justify-center shrink-0">
                    <x-icon name="badge" class="w-5 h-5" />
                </span>
                <h3 class="font-serif-display text-base font-bold text-stone-900 leading-tight">Jumlah Akun Berdasarkan Status</h3>
            </div>

            <div class="space-y-3 my-auto">
                <div class="rounded-2xl bg-[#EFE7D6]/70 border border-stone-200/60 px-4 py-3 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 font-medium text-stone-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#063B25]"></span>
                        Aktif
                    </span>
                    <strong class="text-stone-900 tabular-nums">{{ $akunAktif }} Akun</strong>
                </div>

                <div class="rounded-2xl bg-[#EFE7D6]/70 border border-stone-200/60 px-4 py-3 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 font-medium text-stone-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                        Non Aktif
                    </span>
                    <strong class="text-stone-900 tabular-nums">{{ $akunNonAktif }} Akun</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL 1: ANTREAN VERIFIKASI PEMBAYARAN DP               -->
    <div id="pembayaran-dp" class="bg-white rounded-[32px] p-6 sm:p-8 shadow-sm border border-stone-200/80 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Modul Verifikasi Keuangan</span>
                <h3 class="text-xl font-bold text-[#0B3B24]">Daftar Pembayaran Uang Muka (DP) Menunggu Persetujuan</h3>
            </div>
            <span class="bg-amber-100 text-amber-900 text-xs font-bold px-3 py-1.5 rounded-full font-sans">
                {{ $pembayaranPending->count() + ($bookingMenungguDp ?? collect())->count() }} Perlu Diproses
            </span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200 font-sans">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#0B3B24] text-white">
                        <th class="p-3.5 font-semibold">Kode Booking</th>
                        <th class="p-3.5 font-semibold">Nama Jamaah</th>
                        <th class="p-3.5 font-semibold">Paket Umroh/Haji</th>
                        <th class="p-3.5 font-semibold">Nominal Uang Muka (DP)</th>
                        <th class="p-3.5 font-semibold text-center">Bukti Transfer</th>
                        <th class="p-3.5 font-semibold text-center">Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 text-stone-700">
                    @forelse($pembayaranPending as $bayar)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="p-3.5 font-bold text-[#0B3B24] font-mono">
                            {{ $bayar->invoice->booking->kode_booking ?? '-' }}
                        </td>
                        <td class="p-3.5">
                            <strong class="text-stone-900 block text-xs">{{ $bayar->invoice->booking->user->name ?? 'Jamaah' }}</strong>
                            <span class="text-[11px] text-stone-500">{{ $bayar->invoice->booking->user->email ?? '-' }}</span>
                        </td>
                        <td class="p-3.5">
                            {{ $bayar->invoice->booking->jadwal->paket->nama_paket ?? 'Paket Umroh' }}
                        </td>
                        <td class="p-3.5">
                            <span class="font-bold text-emerald-800 text-sm block">{{ \App\Helpers\RozwiaHelper::formatRupiah($bayar->amount) }}</span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                Uang Muka (DP)
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            @if($bayar->bukti_transfer)
                                <a href="{{ route('admin.pembayaran.download', $bayar->id) }}" target="_blank" class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-300 px-3 py-1 rounded-lg text-[11px] font-semibold hover:bg-emerald-100 transition">
                                    <x-icon name="search" class="w-3.5 h-3.5" /> <span>Lihat Bukti</span>
                                </a>
                            @else
                                <span class="text-stone-400 italic">Tidak Ada File</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('admin.pembayaran.verify', $bayar->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI pembayaran DP sebesar {{ \App\Helpers\RozwiaHelper::formatRupiah($bayar->amount) }} milik {{ addslashes($bayar->invoice->booking->user->name ?? 'Jamaah') }} ini?')">
                                    @csrf
                                    <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-3 py-1.5 rounded-xl text-[11px] transition cursor-pointer shadow-xs inline-flex items-center gap-1">
                                        <x-icon name="check_circle" class="w-3.5 h-3.5" /> <span>Setujui</span>
                                    </button>
                                </form>
                                <form action="{{ route('admin.pembayaran.reject', $bayar->id) }}" method="POST" onsubmit="return confirm('Tolak pembayaran DP ini?')">
                                    @csrf
                                    <input type="hidden" name="admin_notes" value="Bukti transfer tidak valid atau belum masuk ke rekening.">
                                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-3 py-1.5 rounded-xl text-[11px] transition cursor-pointer shadow-xs inline-flex items-center gap-1">
                                        <x-icon name="close" class="w-3.5 h-3.5" /> <span>Tolak</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @foreach($bookingMenungguDp ?? [] as $bDp)
                    @php
                        $dpTagihan = (int) ($bDp->invoices->first()?->amount ?? ($bDp->total_harga * 0.10));
                    @endphp
                    <tr class="hover:bg-stone-50 transition bg-amber-50/30">
                        <td class="p-3.5 font-bold text-[#0B3B24] font-mono">
                            {{ $bDp->kode_booking }}
                        </td>
                        <td class="p-3.5">
                            <strong class="text-stone-900 block text-xs">{{ $bDp->user->name ?? 'Jamaah' }}</strong>
                            <span class="text-[11px] text-stone-500">{{ $bDp->user->email ?? '-' }}</span>
                        </td>
                        <td class="p-3.5">
                            {{ $bDp->jadwal->paket->nama_paket ?? 'Paket Umroh' }}
                            <span class="block text-[10px] text-stone-500">{{ $bDp->jumlah_jamaah }} Jamaah</span>
                        </td>
                        <td class="p-3.5">
                            <span class="font-bold text-emerald-800 text-sm block">{{ \App\Helpers\RozwiaHelper::formatRupiah($dpTagihan) }}</span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                Menunggu DP (WA)
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg inline-flex items-center gap-1">
                                <x-icon name="mail" class="w-3.5 h-3.5" /> <span>Cek Bukti di WA Admin</span>
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <form action="{{ route('admin.booking.confirm-payment', $bDp->id) }}" method="POST" class="inline-flex items-center justify-center" onsubmit="return confirm('Setujui DP untuk {{ addslashes($bDp->user->name ?? 'Jamaah') }} dan kirimkan Email + Password acak ke {{ addslashes($bDp->user->email ?? '') }}?')">
                                @csrf
                                <input type="hidden" name="amount" value="{{ $dpTagihan }}">
                                <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-3.5 py-1.5 rounded-xl text-[11px] transition cursor-pointer shadow-xs whitespace-nowrap inline-flex items-center gap-1">
                                    <x-icon name="check_circle" class="w-3.5 h-3.5" /> <span>DP Verified</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach

                    @if($pembayaranPending->isEmpty() && ($bookingMenungguDp ?? collect())->isEmpty())
                    <tr>
                        <td colspan="6" class="p-6 text-center text-stone-500 italic">
                            Alhamdulillah, saat ini tidak ada antrean verifikasi pembayaran DP yang tertunda.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    @if(session('wa_reply_url'))
        // Scroll ke atas agar notifikasi dan tombol balas WhatsApp langsung terlihat
        window.scrollTo({ top: 0, behavior: 'smooth' });
        sessionStorage.removeItem('admin_dashboard_scroll');
    @else
        const savedY = sessionStorage.getItem('admin_dashboard_scroll');
        if (savedY !== null) {
            window.scrollTo({ top: parseInt(savedY, 10), behavior: 'instant' });
            sessionStorage.removeItem('admin_dashboard_scroll');
        }
    @endif
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            sessionStorage.setItem('admin_dashboard_scroll', window.scrollY);
        });
    });
});
</script>
@endsection

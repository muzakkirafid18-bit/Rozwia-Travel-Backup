<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="darkreader-lock">
    <title>{{ ($totalDibayar > 0 ? 'Konfirmasi Pelunasan via WhatsApp' : 'Konfirmasi Pembayaran DP via WhatsApp') }} - ROZWIA Islamic Travel</title>
    @include('partials.favicon')

    <!-- styling vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            color-scheme: light only;
        }
        .font-serif-display {
            font-family: 'Abhaya Libre', 'Playfair Display', serif;
        }
    </style>
</head>

<body class="bg-[#F4EFE6] font-sans text-stone-800 antialiased min-h-screen py-8 px-4 sm:px-6 lg:px-8">

@php
    $isPelunasan = ($totalDibayar > 0);
    $defaultNominal = $isPelunasan && $sisaTagihan > 0 ? (int)$sisaTagihan : (int)($invoice->amount ?? 5000000);
    $jenisLabel = $isPelunasan ? 'Pelunasan Biaya Paket' : 'Uang Muka (DP)';
    $waNumber = '6285122978237';
    $namaPaket = $booking->jadwal->paket->nama_paket ?? 'Paket Umroh';
@endphp

<div class="max-w-4xl mx-auto space-y-6 font-serif"
     x-data="{
        nominal: {{ $defaultNominal }},
        formatRupiah(angka) {
            return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
        },
        get waUrl() {
            const pesan = 'Assalamualaikum Admin ROZWIA,\n\n' +
                'Saya ingin melakukan konfirmasi pembayaran *{{ addslashes($jenisLabel) }}*:\n' +
                '• Kode Booking: *{{ addslashes($booking->kode_booking) }}*\n' +
                '• Nama Pemesan: *{{ addslashes($booking->nama_rombongan) }}*\n' +
                '• Email Akun: {{ addslashes($user->email) }}\n' +
                '• Paket: {{ addslashes($namaPaket) }}\n' +
                '• Nominal Transfer: *' + this.formatRupiah(this.nominal) + '*\n\n' +
                'Berikut saya lampirkan foto/bukti transfernya. Mohon bantuannya untuk verifikasi. Terima kasih.';
            return 'https://wa.me/{{ $waNumber }}?text=' + encodeURIComponent(pesan);
        }
     }">

    <!-- bar atas -->
    <div class="flex items-center justify-between gap-4 pb-2">
        <a href="{{ auth()->check() ? route('user.dashboard') : route('landing') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-white border border-stone-200 text-[#0B3B24] hover:bg-stone-50 font-bold text-xs transition shadow-xs cursor-pointer">
            <x-icon name="arrow_back" class="w-4 h-4" />
            <span>Kembali ke Beranda {{ auth()->check() ? 'Jamaah' : '' }}</span>
        </a>

        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/lOGO rozwia.png') }}" alt="Logo ROZWIA" class="h-9 w-auto object-contain">
            <span class="font-serif-display font-bold text-lg text-[#0B3B24] hidden sm:inline">ROZWIA</span>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-100 border border-emerald-300 rounded-2xl text-emerald-900 text-sm flex items-center gap-3">
        <x-icon name="check_circle" class="w-5 h-5 text-emerald-700" />
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-100 border border-rose-300 rounded-2xl text-rose-900 text-sm flex items-center gap-3">
        <x-icon name="warning" class="w-5 h-5 text-rose-700" />
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Card Utama Pembayaran -->
    <div class="bg-white p-6 sm:p-10 rounded-[32px] shadow-sm border border-stone-200/80 space-y-8">
        
        <div class="border-b border-stone-100 pb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-amber-800 font-bold uppercase tracking-widest text-xs">
                    {{ $isPelunasan ? 'Tahap Pelunasan Biaya' : 'Langkah 2 dari 3' }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold text-[#0B3B24] mt-1 font-serif-display">
                    {{ $isPelunasan ? 'Pembayaran Pelunasan via WhatsApp' : 'Pembayaran Uang Muka (DP) via WhatsApp' }}
                </h1>
                <p class="text-stone-600 text-xs sm:text-sm mt-1 font-sans">
                    {{ $isPelunasan ? 'Silakan transfer sisa pelunasan ke rekening resmi ROZWIA, lalu kirimkan bukti transfernya langsung ke WhatsApp Admin.' : 'Silakan transfer DP ke rekening resmi ROZWIA, lalu kirimkan foto/struk bukti transfer langsung ke WhatsApp Admin.' }}
                </p>
            </div>
        </div>

        @php
            $dpSapaan = \App\Helpers\RozwiaHelper::userSalutation($user, $booking?->jamaahDetails?->first());
        @endphp
        @guest
        <!-- info aktivasi akun lewat email -->
        <div class="p-4 bg-amber-50/80 border border-amber-200/80 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 font-sans">
            <div class="space-y-1">
                <span class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                    <x-icon name="lock" class="w-4 h-4 text-amber-700" /> Informasi Aktivasi Akun Portal Jamaah:
                </span>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Email terdaftar: <strong class="text-emerald-950 font-mono">{{ $user->email }}</strong>. Password acak sementara akan dikirimkan ke email {{ $dpSapaan }} setelah pembayaran DP diverifikasi oleh Admin (wajib diganti saat login pertama).
                </p>
            </div>
            <span class="text-[11px] text-amber-800 bg-amber-100/60 border border-amber-300/80 px-3 py-1.5 rounded-xl font-medium shrink-0 inline-flex items-center gap-1.5">
                <x-icon name="mail" class="w-3.5 h-3.5" /> Dikirim ke Email
            </span>
        </div>
        @endguest

        <!-- Ringkasan Paket & Tagihan -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 font-sans">
            <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200/60">
                <span class="block text-[10px] text-stone-500 uppercase tracking-wider font-bold">Paket Dipilih</span>
                <strong class="text-sm text-[#0B3B24] mt-1 block font-serif-display">{{ $namaPaket }}</strong>
                <span class="text-xs text-stone-500">{{ $booking->catatan ?? 'Tipe Kamar: Quad' }}</span>
            </div>
            <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200/60">
                <span class="block text-[10px] text-stone-500 uppercase tracking-wider font-bold">Total Biaya Paket</span>
                <strong class="text-sm text-[#0B3B24] mt-1 block font-serif-display">{{ \App\Helpers\RozwiaHelper::formatRupiah($totalBiaya) }}</strong>
                <span class="text-xs text-stone-500">{{ $booking->jumlah_jamaah }} Orang Jamaah</span>
            </div>
            <div class="p-4 {{ $isPelunasan ? 'bg-amber-50 border-amber-200/60' : 'bg-emerald-50 border-emerald-200/60' }} rounded-2xl border">
                <span class="block text-[10px] {{ $isPelunasan ? 'text-amber-800' : 'text-emerald-800' }} uppercase tracking-wider font-bold">
                    {{ $isPelunasan ? 'Sisa Tagihan Pelunasan' : 'Nominal DP Wajib' }}
                </span>
                <strong class="text-lg {{ $isPelunasan ? 'text-amber-950' : 'text-emerald-950' }} mt-1 block font-mono font-bold">
                    {{ \App\Helpers\RozwiaHelper::formatRupiah($isPelunasan ? $sisaTagihan : ($invoice->amount ?? 5000000)) }}
                </strong>
                <span class="text-[11px] {{ $isPelunasan ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $isPelunasan ? 'Sudah dibayar: ' . \App\Helpers\RozwiaHelper::formatRupiah($totalDibayar) : 'DP untuk ' . $booking->jumlah_jamaah . ' Orang' }}
                </span>
            </div>
        </div>

        <!-- Rekening Resmi Perusahaan -->
        <div class="bg-[#0B3B24] text-white p-6 sm:p-8 rounded-3xl space-y-4 relative overflow-hidden shadow-md">
            <div class="flex items-center justify-between border-b border-emerald-800/80 pb-3">
                <span class="text-xs uppercase tracking-widest text-[#D4B996] font-bold">
                    {{ $isPelunasan ? 'Rekening Resmi Pembayaran Pelunasan' : 'Rekening Resmi Pembayaran DP' }}
                </span>
                <span class="bg-emerald-900/80 border border-emerald-700/80 text-amber-300 text-[10px] font-bold px-3 py-1 rounded-full uppercase">100% Terverifikasi</span>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <span class="text-xs text-emerald-200">{{ $rekening->nama_bank ?? 'Bank Syariah Indonesia (BSI)' }}</span>
                    <div class="text-2xl sm:text-3xl font-bold font-mono tracking-wider text-amber-300 mt-1">
                        {{ $rekening->nomor_rekening ?? '7123456789' }}
                    </div>
                    <span class="text-xs text-emerald-100 block mt-1">a.n. {{ $rekening->atas_nama ?? 'PT Pangrango Berkah Digital' }}</span>
                </div>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $rekening->nomor_rekening ?? '7123456789' }}'); alert('Nomor rekening berhasil disalin!');" class="bg-emerald-900 hover:bg-emerald-800 text-white border border-emerald-700 px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer inline-flex items-center gap-1.5">
                    <x-icon name="copy" class="w-4 h-4" />
                    <span>Salin No. Rekening</span>
                </button>
            </div>

            <p class="text-[11px] text-emerald-200/80 leading-relaxed pt-2 border-t border-emerald-800/80 font-sans">
                Batas waktu transfer: <strong class="text-amber-300">{{ isset($invoice) && $invoice?->batas_pembayaran ? $invoice->batas_pembayaran->format('d/m/Y, H:i') : now()->addDays(3)->format('d/m/Y, H:i') }} WIB</strong>. Setelah transfer, silakan klik tombol WhatsApp di bawah untuk mengirimkan foto bukti transfer ke Admin.
            </p>
        </div>

        <!-- Konfirmasi Pembayaran via WhatsApp Admin -->
        <div class="bg-[#FBF9F3] border border-stone-200 rounded-3xl p-6 sm:p-8 space-y-5">
            <div class="space-y-2 font-sans">
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                    {{ $isPelunasan ? 'Nominal Pelunasan (Sesuai Sisa Tagihan)' : 'Nominal DP Wajib (Sesuai Tagihan)' }}
                </label>
                <div class="w-full bg-stone-100 border border-stone-300 rounded-2xl px-4 py-3.5 text-sm text-stone-800 font-mono font-bold select-none flex items-center justify-between">
                    <span x-text="formatRupiah(nominal)">{{ \App\Helpers\RozwiaHelper::formatRupiah($defaultNominal) }}</span>
                    <span class="text-[10px] font-sans font-semibold bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full">Otomatis dari Tagihan</span>
                </div>
                <p class="text-[11px] text-stone-500">Nominal di atas dikunci otomatis sesuai sisa tagihan dan langsung dimasukkan ke pesan WhatsApp {{ $dpSapaan }} ke Admin.</p>
            </div>

            <div class="space-y-4 pt-2 font-sans">
                <a :href="waUrl" target="_blank" class="w-full bg-[#25D366] hover:bg-[#20ba5a] active:scale-98 text-white font-bold py-4 px-6 rounded-2xl shadow-lg transition-all text-xs sm:text-sm uppercase tracking-wider text-center flex items-center justify-center gap-2.5 cursor-pointer">
                    <x-icon name="send" class="w-4 h-4" />
                    <span>Kirim Bukti {{ $isPelunasan ? 'Pelunasan' : 'DP' }} via WhatsApp Admin (0851-2297-8237)</span>
                </a>

                @auth
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-stone-200/80 text-xs">
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold px-4 py-2.5 shadow-sm transition">
                                <x-icon name="arrow_back" class="w-4 h-4" />
                                <span>Kembali ke Dashboard Jamaah</span>
                            </a>
                            <a href="{{ route('user.kuitansi') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-[#B78616] bg-white hover:bg-amber-50 text-[#B78616] font-bold px-4 py-2.5 transition">
                                <x-icon name="receipt_long" class="w-4 h-4" />
                                <span>Cetak Kuitansi (PDF)</span>
                            </a>
                        </div>
                        <span class="text-[11px] text-stone-500">Status pembayaran diperbarui otomatis setelah diverifikasi Admin.</span>
                    </div>
                @else
                    <!-- Panduan Langkah Selanjutnya & Tombol Masuk ke Login -->
                    <div class="rounded-2xl bg-white border border-stone-200/90 p-5 space-y-4 shadow-2xs">
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                                <x-icon name="info" class="w-4 h-4" />
                            </span>
                            <div class="space-y-1">
                                <h3 class="font-serif-display text-base font-bold text-[#0B3B24]">Apa Langkah Setelah Mengirim Bukti DP?</h3>
                                <ol class="text-xs text-stone-600 space-y-1 list-decimal list-inside leading-relaxed">
                                    <li>Kirim foto/struk transfer via tombol WhatsApp Admin di atas.</li>
                                    <li>Admin memverifikasi DP, lalu <strong>password sementara</strong> otomatis dikirim ke email <span class="font-mono font-semibold text-stone-800">{{ $user->email }}</span>.</li>
                                    <li>Klik tombol <strong>Login ke Akun ROZWIA</strong> di bawah untuk melengkapi profil &amp; unggah berkas persyaratan.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="pt-1 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <a href="{{ route('login') }}"
                               class="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold py-3.5 px-6 text-xs sm:text-sm shadow-md transition">
                                <x-icon name="login" class="w-4 h-4" />
                                <span>Sudah Dikonfirmasi? Login ke Akun ROZWIA</span>
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

    </div>

</div>

</body>
</html>

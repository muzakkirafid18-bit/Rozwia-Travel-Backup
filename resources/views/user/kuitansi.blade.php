<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Resmi - {{ $booking->kode_booking }} | ROZWIA Islamic Travel</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .font-serif-display { font-family: 'Abhaya Libre', serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; padding: 0 !important; }
            .invoice-sheet { box-shadow: none !important; border: none !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-[#EAE0D0] font-sans text-stone-800 antialiased py-8 px-4">

    <!-- tombol aksi cetak -->
    <div class="max-w-4xl mx-auto mb-5 flex flex-wrap items-center justify-between gap-3 no-print">
        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-2 rounded-full bg-white border border-stone-200 px-4 py-2 text-xs font-bold text-[#063B25] hover:bg-stone-50 shadow-xs">
            <span>&larr;</span> <span>Kembali ke Beranda Jamaah</span>
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-full bg-[#063B25] hover:bg-emerald-900 text-white px-5 py-2.5 text-xs font-bold shadow-md cursor-pointer transition">
            <span>🖨️</span> <span>Cetak / Simpan sebagai PDF</span>
        </button>
    </div>

    <!-- lembar kuitansi -->
    <div class="invoice-sheet max-w-4xl mx-auto bg-white rounded-[28px] shadow-xl border border-stone-200 overflow-hidden">

        <!-- kop kuitansi -->
        <div class="bg-[#063B25] text-white p-7 sm:p-9 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA" class="h-14 w-auto object-contain">
                <div>
                    <h1 class="font-serif-display text-2xl sm:text-3xl font-bold tracking-wide">{{ $company['nama_perusahaan'] ?? 'ROZWIA Islamic Travel' }}</h1>
                    <p class="text-[11px] text-amber-300 font-semibold">{{ $company['no_izin'] ?? 'PPIU No. 1023/2021' }}</p>
                    <p class="text-[11px] text-emerald-100/80 mt-0.5">{{ $company['email'] ?? '' }} · {{ $company['telepon'] ?? '' }}</p>
                </div>
            </div>
            <div class="sm:text-right">
                <span class="text-[10px] uppercase tracking-widest text-amber-300 font-bold block">Dokumen Resmi</span>
                <h2 class="font-serif-display text-2xl font-bold">INVOICE &amp; KUITANSI</h2>
                <p class="text-xs font-mono text-emerald-100 mt-1">{{ $booking->kode_booking }}</p>
            </div>
        </div>

        <div class="p-7 sm:p-9 space-y-7 text-xs">

            <!-- info jamaah & paket -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-stone-200">
                <div class="space-y-1.5">
                    @php
                        $sapaan = \App\Helpers\RozwiaHelper::userSalutation($user, $booking->jamaahDetails->first());
                    @endphp
                    <span class="text-[10px] uppercase tracking-widest font-bold text-stone-400">Diterbitkan Untuk</span>
                    <p class="text-base font-bold text-[#063B25]">{{ $sapaan }} {{ $user->name }}</p>
                    <p class="text-stone-600">Email: {{ $user->email }}</p>
                    <p class="text-stone-600">No. WhatsApp: {{ $user->nomor_telepon ?: '-' }}</p>
                    <p class="text-stone-600">Jumlah Jamaah: <strong>{{ $booking->jumlah_jamaah }} Orang</strong></p>
                </div>
                <div class="space-y-1.5 sm:text-right">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-stone-400">Detail Paket &amp; Keberangkatan</span>
                    <p class="text-base font-bold text-[#063B25]">{{ $booking->jadwal?->paket?->nama_paket ?? 'Paket Umroh/Haji' }}</p>
                    <p class="text-stone-600">{{ $booking->catatan ?: '-' }}</p>
                    <p class="text-stone-600">
                        Keberangkatan: <strong>{{ $booking->jadwal?->tanggal_keberangkatan?->format('d/m/Y') ?? '-' }}</strong>
                    </p>
                    <p class="mt-2">
                        @if($sisaTagihan <= 0 && $totalBiaya > 0)
                            <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold uppercase text-[11px]">✓ LUNAS SEPENUHNYA</span>
                        @elseif($totalDibayar > 0)
                            <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300 font-bold uppercase text-[11px]">● DP TERVERIFIKASI</span>
                        @else
                            <span class="inline-block px-3 py-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 font-bold uppercase text-[11px]">⏳ MENUNGGU PEMBAYARAN</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- rincian pembayaran -->
            <div class="space-y-3">
                <h3 class="font-bold text-sm text-[#063B25]">Rincian Pembayaran Terverifikasi</h3>
                <div class="overflow-x-auto rounded-2xl border border-stone-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-100 text-stone-600 text-[11px]">
                                <th class="py-3 px-4 font-bold">Tanggal Verifikasi</th>
                                <th class="py-3 px-4 font-bold">Keterangan / Metode</th>
                                <th class="py-3 px-4 font-bold text-right">Nominal Masuk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($payments as $pay)
                            <tr>
                                <td class="py-3 px-4 text-stone-600">{{ ($pay->verified_at ?? $pay->created_at)?->timezone('Asia/Jakarta')->format('d/m/Y, H:i') }} WIB</td>
                                <td class="py-3 px-4 font-semibold text-stone-800">{{ $pay->metode_pembayaran ?? 'Transfer Bank' }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">{{ \App\Helpers\RozwiaHelper::formatRupiah($pay->amount) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-6 px-4 text-center text-stone-500 italic">Belum ada pembayaran yang diverifikasi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ringkasan total -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 pt-2">
                <div class="text-[11px] text-stone-500 max-w-sm space-y-1">
                    <p class="font-bold text-stone-700">Alamat Kantor:</p>
                    <p>{{ $company['alamat'] ?? '' }}</p>
                    <p class="pt-1 italic">Dokumen ini diterbitkan secara elektronik oleh sistem ROZWIA Islamic Travel dan sah tanpa tanda tangan basah.</p>
                </div>
                <div class="w-full sm:w-80 rounded-2xl bg-stone-50 border border-stone-200 p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-stone-600">Total Biaya Paket</span>
                        <strong class="font-mono">{{ \App\Helpers\RozwiaHelper::formatRupiah($totalBiaya) }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-600">Total Sudah Dibayar</span>
                        <strong class="font-mono text-emerald-700">{{ \App\Helpers\RozwiaHelper::formatRupiah($totalDibayar) }}</strong>
                    </div>
                    <div class="pt-2 border-t border-stone-200 flex justify-between text-sm">
                        <span class="font-bold text-[#063B25]">Sisa Tagihan</span>
                        <strong class="font-mono {{ $sisaTagihan > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                            {{ \App\Helpers\RozwiaHelper::formatRupiah($sisaTagihan) }}
                        </strong>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>

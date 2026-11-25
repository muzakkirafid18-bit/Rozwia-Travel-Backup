@extends('layouts.app')

@section('title', 'Paket Umroh - Rozwia Islamic Travel')

@section('content')

<!-- wrapper halaman -->
<div class="w-full bg-[#EAE0D0] min-h-screen pb-24 pt-28 sm:pt-32">
    
    <!-- banner paket umroh -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
        <div class="relative rounded-[24px] bg-[#0B3B24] overflow-hidden shadow-lg p-8 sm:p-12">
            <div class="absolute inset-0 opacity-35 bg-cover bg-center" style="background-image: url('{{ asset('images/Hero.png') }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[#0B3B24]/90 via-[#0B3B24]/60 to-transparent"></div>
            
            <div class="relative z-10 space-y-2">
                <p class="text-xs text-amber-300 font-serif tracking-widest uppercase">
                    <a href="/" class="hover:underline">Beranda</a> &gt; <span class="text-white font-medium">Paket Umroh</span>
                </p>
                <h1 class="text-3xl sm:text-5xl font-bold text-white font-serif tracking-tight">
                    Paket Perjalanan Umroh
                </h1>
                <p class="text-emerald-100/90 text-sm sm:text-base max-w-xl font-serif leading-relaxed">
                    Pilih paket dan jadwal keberangkatan umroh yang sesuai dengan kebutuhan Anda.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        @php
            // Ambil filter yang sedang aktif dari URL (default: 'semua')
            $activeFilter = request('filter', 'semua');

            // Ambil langsung dari $pakets yang dikirim controller (Database)
            $umrohList = $pakets->map(function($p) {
                $jadwal = $p->jadwals->first();
                $diffDays = $jadwal ? $jadwal->tanggal_keberangkatan->diffInDays($jadwal->tanggal_pulang) : 9;
                $durasi = $diffDays . ' Hari';

                $rawImg = $p->gambar_utama;
                if (empty($rawImg) || str_contains($rawImg, 'images.unsplash.com')) {
                    $img = asset('images/pilihan paket.jpg');
                } elseif (str_starts_with($rawImg, 'http')) {
                    $img = $rawImg;
                } else {
                    $img = asset(ltrim($rawImg, '/'));
                }

                $layananNama = strtolower($p->jenisLayanan?->nama_pelayanan ?? '');
                $namaLower = strtolower($p->nama_paket);

                $isUmrohPlus = str_contains($layananNama, 'plus') || str_contains($namaLower, 'plus') || str_contains($namaLower, 'turki') || str_contains($namaLower, 'dubai') || str_contains($namaLower, 'istanbul') || str_contains($namaLower, 'aqsha');
                $isRamadhan = str_contains($namaLower, 'ramadhan') || str_contains($namaLower, 'lailatur') || str_contains($namaLower, 'syawal') || str_contains($layananNama, 'ramadhan');

                $filterKey = '9-hari';
                if ($diffDays >= 14 || str_contains($namaLower, '15 hari')) {
                    $filterKey = '15-hari';
                } elseif ($diffDays >= 11 || str_contains($namaLower, '12 hari')) {
                    $filterKey = '12-hari';
                } elseif ($isUmrohPlus) {
                    $filterKey = 'umroh-plus';
                }

                $hotelNama = $p->paketTipeKamars->first()?->hotel?->nama_hotel ?? 'Hotel Bintang 5';

                $tag = $p->jenisLayanan?->nama_pelayanan ?? '';
                if (str_contains($namaLower, 'istanbul')) $tag = 'Terlaris';
                elseif (str_contains($namaLower, 'serenity')) $tag = 'Favorit';

                return [
                    'id' => $p->id,
                    'title' => $p->nama_paket,
                    'desc' => $p->deskripsi ?? 'Perjalanan Umroh yang nyaman dan terencana untuk meraih keberkahan di Tanah Suci.',
                    'days' => $durasi,
                    'diff_days' => $diffDays,
                    'filter_key' => $filterKey,
                    'is_umroh_plus' => $isUmrohPlus,
                    'is_ramadhan' => $isRamadhan,
                    'route' => 'Makkah & Madinah',
                    'hotel' => $hotelNama,
                    'price' => \App\Helpers\RozwiaHelper::formatRupiah($p->quadPrice()),
                    'tag' => $tag,
                    'img' => $img,
                ];
            });

            // Filter data jika tombol kategori dipilih
            $filteredList = $umrohList->filter(function($item) use ($activeFilter) {
                if ($activeFilter === 'semua') return true;
                if ($activeFilter === 'ramadhan') return $item['is_ramadhan'];
                if ($activeFilter === '9-hari') return $item['diff_days'] <= 10;
                if ($activeFilter === '12-hari') return $item['diff_days'] >= 11 && $item['diff_days'] <= 13;
                if ($activeFilter === '15-hari') return $item['diff_days'] >= 14 && $item['diff_days'] <= 18;
                if ($activeFilter === 'umroh-plus') return $item['is_umroh_plus'];
                return $item['filter_key'] === $activeFilter;
            });
        @endphp

        <!-- FILTER KATEGORI PILL -->
        <div class="flex flex-wrap items-center gap-2 pt-2">
            <a href="{{ route('paket.umroh', ['filter' => 'semua']) }}" class="{{ $activeFilter === 'semua' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">Semua</a>
            
            <a href="{{ route('paket.umroh', ['filter' => '9-hari']) }}" class="{{ $activeFilter === '9-hari' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">9 Hari</a>
            
            <a href="{{ route('paket.umroh', ['filter' => '12-hari']) }}" class="{{ $activeFilter === '12-hari' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">12 Hari</a>
            
            <a href="{{ route('paket.umroh', ['filter' => '15-hari']) }}" class="{{ $activeFilter === '15-hari' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">15 Hari</a>
            
            <a href="{{ route('paket.umroh', ['filter' => 'ramadhan']) }}" class="{{ $activeFilter === 'ramadhan' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">Paket Ramadhan</a>
            
            <a href="{{ route('paket.umroh', ['filter' => 'umroh-plus']) }}" class="{{ $activeFilter === 'umroh-plus' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">Umroh Plus</a>
        </div>

        <!-- GRID KATALOG UMROH -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            @forelse($filteredList as $item)
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-stone-200/80 flex flex-col justify-between space-y-5">
                <div class="space-y-4">
                    <!-- Gambar Banner Card -->
                    <div class="relative w-full h-56 rounded-2xl overflow-hidden shadow-xs">
                        <img src="{{ $item['img'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover">
                        @if(!empty($item['tag']))
                        <span class="absolute top-3 left-3 bg-[#E5D3B3] text-emerald-950 font-serif text-[10px] font-bold px-3 py-1 rounded-full shadow-xs">
                            {{ $item['tag'] }}
                        </span>
                        @endif
                    </div>

                    <!-- Judul & Deskripsi -->
                    <div class="space-y-2">
                        <h3 class="text-xl font-bold text-[#0B3B24] font-serif">{{ $item['title'] }}</h3>
                        <p class="text-stone-600 text-xs leading-relaxed font-serif">
                            {{ $item['desc'] }}
                        </p>
                    </div>

                    <!-- info ringkas paket -->
                    <div class="bg-[#E5D3B3]/70 rounded-full px-4 py-3 flex items-center justify-between border border-stone-300/40 text-emerald-950 font-serif">
                        <!-- 1. Durasi Hari -->
                        <div class="flex items-center gap-2 px-1">
                            <svg class="w-4 h-4 text-emerald-950 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-[11px] font-bold">{{ $item['days'] }}</span>
                        </div>

                        <div class="w-[1px] h-5 bg-stone-400/50"></div>

                        <!-- 2. Rute Lokasi -->
                        <div class="flex items-center gap-1.5 px-1">
                            <svg class="w-4 h-4 text-emerald-950 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-[10px] font-bold leading-tight">{{ $item['route'] }}</span>
                        </div>

                        <div class="w-[1px] h-5 bg-stone-400/50"></div>

                        <!-- 3. Hotel -->
                        <div class="flex items-center gap-1.5 px-1">
                            <svg class="w-4 h-4 text-emerald-950 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-[10px] font-bold leading-tight">{{ $item['hotel'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- harga & tombol detail -->
                <div class="pt-4 border-t border-stone-200 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-stone-500 block uppercase font-serif">Mulai Dari</span>
                        <span class="text-base font-bold text-stone-900 font-serif">{{ $item['price'] }}</span>
                    </div>
                    <a href="{{ route('paket.detail', $item['id']) }}" class="bg-[#0B3B24] hover:bg-emerald-900 text-white font-serif font-semibold text-xs px-5 py-2.5 rounded-full shadow-sm transition">
                        Detail Paket
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-16 text-stone-600 font-serif">
                <p>Belum ada paket umroh untuk kategori ini.</p>
            </div>
            @endforelse

        </div>

    </div>
</div>

@endsection

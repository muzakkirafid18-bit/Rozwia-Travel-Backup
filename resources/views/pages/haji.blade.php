@extends('layouts.app')

@section('title', 'Paket Haji - Rozwia Islamic Travel')

@section('content')

<!-- wrapper halaman -->
<div class="w-full bg-[#EAE0D0] min-h-screen pb-24 pt-28 sm:pt-32">
    
    <!-- banner paket haji -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
        <div class="relative rounded-[24px] bg-[#0B3B24] overflow-hidden shadow-lg p-8 sm:p-12">
            <div class="absolute inset-0 opacity-35 bg-cover bg-center" style="background-image: url('{{ asset('images/Hero.png') }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[#0B3B24]/90 via-[#0B3B24]/60 to-transparent"></div>
            
            <div class="relative z-10 space-y-2">
                <p class="text-xs text-amber-300 font-serif tracking-widest uppercase">
                    <a href="/" class="hover:underline">Beranda</a> &gt; <span class="text-white font-medium">Paket Haji</span>
                </p>
                <h1 class="text-3xl sm:text-5xl font-bold text-white font-serif tracking-tight">
                    Paket Haji Khusus &amp; Furoda
                </h1>
                <p class="text-emerald-100/90 text-sm sm:text-base max-w-xl font-serif leading-relaxed">
                    Wujudkan perjalanan ibadah haji yang nyaman, amanah, dan berkesan bersama ROZWIA.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        @php
            $activeFilter = request('filter', 'semua');

            // Ambil langsung dari $pakets yang dikirim controller (Database)
            $hajiList = $pakets->map(function($p) {
                $jadwal = $p->jadwals->first();
                $durasi = $jadwal 
                    ? ($jadwal->tanggal_keberangkatan->diffInDays($jadwal->tanggal_pulang) . ' Hari')
                    : '± 25 Hari';

                $rawImg = $p->gambar_utama;
                if (empty($rawImg) || str_contains($rawImg, 'images.unsplash.com')) {
                    $img = asset('images/paket haji.jpg');
                } elseif (str_starts_with($rawImg, 'http')) {
                    $img = $rawImg;
                } else {
                    $img = asset(ltrim($rawImg, '/'));
                }

                $layananNama = strtolower($p->jenisLayanan?->nama_pelayanan ?? '');
                $namaPaketLower = strtolower($p->nama_paket);
                
                $filterKey = 'plus';
                if (str_contains($layananNama, 'furoda') || str_contains($namaPaketLower, 'furoda')) {
                    $filterKey = 'furoda';
                    $tag = 'Furoda 2027';
                } elseif (str_contains($layananNama, 'reguler') || str_contains($namaPaketLower, 'reguler')) {
                    $filterKey = 'reguler';
                    $tag = 'Reguler 2027';
                } else {
                    $filterKey = 'plus';
                    $tag = 'Plus 2027';
                }

                return [
                    'id' => $p->id,
                    'title' => $p->nama_paket,
                    'desc' => $p->deskripsi ?? 'Perjalanan ibadah Haji dengan bimbingan dan pelayanan terbaik bersama ROZWIA.',
                    'days' => $durasi,
                    'filter_key' => $filterKey,
                    'route' => 'Makkah Madinah Arafah Muzdalifah Mina',
                    'price' => \App\Helpers\RozwiaHelper::formatRupiah($p->quadPrice()),
                    'tag' => $p->jenisLayanan?->nama_pelayanan ? ($p->jenisLayanan->nama_pelayanan . ' 2027') : $tag,
                    'img' => $img,
                ];
            });

            $filteredHaji = $hajiList->filter(function($item) use ($activeFilter) {
                if ($activeFilter === 'semua') return true;
                return $item['filter_key'] === $activeFilter;
            });
        @endphp

        <!-- FILTER KATEGORI PILL -->
        <div class="flex flex-wrap items-center gap-2 pt-2">
            <a href="{{ route('paket.haji', ['filter' => 'semua']) }}" class="{{ $activeFilter === 'semua' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">Semua</a>
            
            <a href="{{ route('paket.haji', ['filter' => 'reguler']) }}" class="{{ $activeFilter === 'reguler' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">Haji Reguler</a>
            
            <a href="{{ route('paket.haji', ['filter' => 'plus']) }}" class="{{ $activeFilter === 'plus' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">Haji Plus</a>
            
            <a href="{{ route('paket.haji', ['filter' => 'furoda']) }}" class="{{ $activeFilter === 'furoda' ? 'bg-[#0B3B24] text-white' : 'bg-white hover:bg-emerald-50 text-[#0B3B24]' }} border border-[#0B3B24] text-xs font-semibold px-5 py-2.5 rounded-full shadow-xs transition">Haji Furoda</a>
        </div>

        <!-- GRID KATALOG HAJI -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            @forelse($filteredHaji as $item)
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-stone-200/80 flex flex-col justify-between space-y-5">
                <div class="space-y-4">
                    <div class="relative w-full h-56 rounded-2xl overflow-hidden shadow-xs">
                        <img src="{{ $item['img'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover">
                        @if(!empty($item['tag']))
                        <span class="absolute top-3 left-3 bg-[#E5D3B3] text-emerald-950 font-serif text-[10px] font-bold px-3 py-1 rounded-full shadow-xs">
                            {{ $item['tag'] }}
                        </span>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-xl font-bold text-[#0B3B24] font-serif">{{ $item['title'] }}</h3>
                        <p class="text-stone-600 text-xs leading-relaxed font-serif">
                            {{ $item['desc'] }}
                        </p>
                    </div>

                    <div class="bg-[#E5D3B3]/70 rounded-full px-4 py-3 flex items-center justify-between border border-stone-300/40 text-emerald-950 font-serif">
                        <div class="flex items-center gap-2 px-1">
                            <svg class="w-4 h-4 text-emerald-950 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-[11px] font-bold">{{ $item['days'] }}</span>
                        </div>

                        <div class="w-[1px] h-5 bg-stone-400/50"></div>

                        <div class="flex items-center gap-1.5 px-1 col-span-2">
                            <svg class="w-4 h-4 text-emerald-950 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-[10px] font-bold leading-tight truncate max-w-[180px]">{{ $item['route'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-200 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-stone-500 block uppercase font-serif">Mulai Dari</span>
                        <span class="text-base font-bold text-stone-900 font-serif">{{ $item['price'] }}</span>
                    </div>
                    <!-- card item paket haji -->
                    <a href="{{ route('paket.detail', $item['id']) }}" class="bg-[#0B3B24] hover:bg-emerald-900 text-white font-serif font-semibold text-xs px-5 py-2.5 rounded-full shadow-sm transition">
                        Detail Paket
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-16 text-stone-600 font-serif">
                <p>Belum ada paket haji untuk kategori ini.</p>
            </div>
            @endforelse

        </div>

    </div>
</div>

@endsection

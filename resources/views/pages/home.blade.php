@extends('layouts.app')

@section('title', 'Beranda - Rozwia Islamic Travel')

@section('content')

<div class="w-full space-y-24 bg-[#EAE0D0] pb-24 pt-28 sm:pt-32">

    <!-- bagian hero -->
    <div class="relative mx-auto flex min-h-[560px] sm:min-h-[640px] w-[calc(100%-2rem)] max-w-7xl items-start justify-center overflow-hidden rounded-[24px] bg-[#0B3B24] px-6 pt-16 sm:pt-20 pb-20 shadow-xl sm:w-[calc(100%-4rem)]">
        
        <!-- gambar latar hero -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/makkah home.jpg') }}" alt="Masjidil Haram dan Ka'bah" class="h-full w-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/25 to-transparent"></div>
        </div>

        <!-- Konten Teks Hero di Bagian Atas-Tengah -->
        <div class="relative z-10 mx-auto max-w-4xl space-y-4 text-center font-serif animate-fade-in">
            <span class="block text-xs sm:text-sm font-bold tracking-[0.2em] uppercase text-[#E5C267]">
                SELAMAT DATANG DI ROZWIA
            </span>

            <h1 class="text-3xl sm:text-5xl lg:text-[54px] font-bold tracking-tight text-white leading-tight drop-shadow-md">
                Satu Niat, Satu Tujuan, <br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-amber-200 animate-pulse">
                    Menuju Baitullah.
                </span>
            </h1>

            <p class="text-white/95 text-xs sm:text-base max-w-2xl mx-auto leading-relaxed drop-shadow">
                Awali perjalanan spiritual anda bersama ROZWIA ciptakan pengalaman ibadah yang berkesan.
            </p>
        </div>
    </div>


    <!-- bagian pilihan paket umroh -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        <div class="space-y-2">
            <span class="text-[#8B6A2B] font-serif font-semibold tracking-[0.22em] uppercase text-xs block">PAKET PERJALANAN</span>
            <h2 class="text-3xl sm:text-5xl font-bold text-[#0B3B24] font-serif">Pilihan Paket Umroh</h2>
            <p class="text-stone-700 text-sm sm:text-base font-serif">Temukan pilihan paket dan jadwal keberangkatan umrah.</p>
            <div class="w-16 h-1 bg-[#B78616]/70 rounded-full pt-0.5 mt-3"></div>
        </div>

        @php
            $featuredUmroh = $pakets->filter(function($p) {
                return in_array(strtolower($p->kategori), ['umroh', 'umrah']) && $p->status === 'active';
            })->take(2);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @forelse($featuredUmroh as $p)
                @php
                    $jadwal = $p->jadwals->first();
                    $durasi = $jadwal ? ($jadwal->tanggal_keberangkatan->diffInDays($jadwal->tanggal_pulang) . ' Hari') : '9 Hari';
                    $rawImg = $p->gambar_utama;
                    if (empty($rawImg) || str_contains($rawImg, 'images.unsplash.com')) {
                        $img = asset('images/pilihan paket.jpg');
                    } elseif (str_starts_with($rawImg, 'http')) {
                        $img = $rawImg;
                    } else {
                        $img = asset(ltrim($rawImg, '/'));
                    }
                @endphp
                <div class="bg-[#FBF9F5] rounded-[28px] p-6 sm:p-8 shadow-sm border border-stone-200/80 flex flex-col justify-between hover:shadow-md transition duration-300">
                    <div class="space-y-5">
                        <div class="relative h-60 sm:h-68 rounded-2xl overflow-hidden shadow-xs">
                            <img src="{{ $img }}" alt="{{ $p->nama_paket }}" class="w-full h-full object-cover">
                        </div>

                        <div class="space-y-1 font-serif">
                            <h3 class="text-2xl sm:text-3xl font-bold text-[#0B3B24]">{{ $p->nama_paket }}</h3>
                            <p class="text-xs sm:text-sm text-stone-500">{{ $durasi }} . Makkah &amp; Madinah</p>
                        </div>

                        <div class="text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif tabular-nums">
                            {{ \App\Helpers\RozwiaHelper::formatRupiah($p->quadPrice()) }}
                        </div>

                        <div class="pt-4 border-t border-stone-200 flex items-end justify-between gap-4">
                            <div class="space-y-1.5 font-serif">
                                <span class="text-xs sm:text-sm font-bold text-stone-900 block">Fasilitas:</span>
                                <ul class="text-xs sm:text-sm text-stone-500 space-y-0.5">
                                    @php
                                        $homeFasilitas = is_array($p->fasilitas) && count($p->fasilitas) > 0
                                            ? array_slice($p->fasilitas, 0, 2)
                                            : ['Hotel Makkah & Madinah', 'Tiket Pesawat & Visa'];
                                    @endphp
                                    @foreach($homeFasilitas as $hf)
                                        <li>{{ $hf }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <a href="{{ route('paket.detail', $p->id) }}" class="bg-[#123724] hover:bg-[#0B2919] text-white font-serif font-medium text-xs sm:text-sm px-7 py-2.5 rounded-full border-2 border-[#C59B40] shadow-sm transition transform hover:-translate-y-0.5 shrink-0">
                                Detail Paket
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-10 font-serif text-stone-500">
                    <p>Paket Umroh segera hadir.</p>
                </div>
            @endforelse
        </div>

        <!-- tombol lihat semua paket umroh -->
        <div class="pt-4 flex justify-center">
            <a href="{{ route('paket.umroh') }}" class="bg-[#F59100] hover:bg-[#e08500] text-[#0B3B24] font-serif font-bold text-base sm:text-xl px-9 py-4 rounded-full shadow-md transition flex items-center gap-3 transform hover:-translate-y-0.5">
                <span>Lihat semua paket umrah</span>
                <span class="w-7 h-7 rounded-full border-2 border-[#0B3B24] flex items-center justify-center text-sm font-bold">&rarr;</span>
            </a>
        </div>

    </section>


    <!-- bagian program haji khusus -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            
            <!-- Kiri: Foto Kubah / Arch "Dream Hajj" -->
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md overflow-hidden rounded-t-[240px] shadow-xl border-4 border-[#FBF9F5]">
                    <img src="{{ asset('images/paket haji.jpg') }}" alt="Program Haji Khusus ROZWIA" class="w-full h-[460px] sm:h-[520px] object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-black/15"></div>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <span class="font-serif italic text-4xl sm:text-5xl text-white/95 drop-shadow-lg tracking-wide">Dream Hajj</span>
                    </div>
                </div>
            </div>

            <!-- Kanan: Detail Program Haji Khusus -->
            <div class="lg:col-span-7 space-y-5 font-serif">
                <span class="inline-block rounded-full border border-[#C59B40] bg-[#FBF9F3] px-6 py-2 text-xs sm:text-sm font-semibold tracking-wider text-[#0B3B24] shadow-2xs">
                    PROGRAM HAJI KHUSUS
                </span>

                <h2 class="text-3xl sm:text-5xl font-bold text-[#0B3B24] leading-tight">
                    Paket Haji Khusus ROZWIA
                </h2>

                <p class="text-stone-700 text-sm sm:text-base leading-relaxed max-w-xl">
                    Wujudkan perjalanan ibadah haji yang nyaman dan berkesan bersama ROZWIA dengan pelayanan serta pendamping selama perjalanan.
                </p>

                <p class="text-xs sm:text-sm font-semibold text-[#C96B00]">
                    Pendaftaran dibuka . Keberangkatan 2027
                </p>

                <!-- 3 Pill Fitur Putih -->
                <div class="space-y-3 max-w-md pt-1">
                    <div class="bg-[#FBF9F5] rounded-full px-6 py-3.5 shadow-xs border border-stone-200/70 flex items-center gap-4">
                        <x-icon name="travel" class="w-5 h-5 text-amber-500" />
                        <span class="text-sm sm:text-base font-medium text-[#0B3B24]">Jadwal keberangkatan</span>
                    </div>
                    <div class="bg-[#FBF9F5] rounded-full px-6 py-3.5 shadow-xs border border-stone-200/70 flex items-center gap-4">
                        <x-icon name="group" class="w-5 h-5 text-amber-500" />
                        <span class="text-sm sm:text-base font-medium text-[#0B3B24]">Pendamping Jamaah</span>
                    </div>
                    <div class="bg-[#FBF9F5] rounded-full px-6 py-3.5 shadow-xs border border-stone-200/70 flex items-center gap-4">
                        <x-icon name="home" class="w-5 h-5 text-amber-500" />
                        <span class="text-sm sm:text-base font-medium text-[#0B3B24]">Akomodasi Premium</span>
                    </div>
                </div>

                <!-- 2 Tombol CTA Haji -->
                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <a href="{{ route('paket.haji') }}" class="bg-[#0B3B24] hover:bg-emerald-900 text-white font-medium text-sm px-8 py-3 rounded-full shadow-md transition transform hover:-translate-y-0.5">
                        Lihat Detail Haji
                    </a>
                    <a href="https://wa.me/6285122978237?text=Assalamualaikum%20ROZWIA,%20saya%20ingin%20konsultasi%20mengenai%20Paket%20Haji%20Khusus." target="_blank" class="bg-[#C5CDC6] hover:bg-[#b5bfb6] text-[#0B3B24] border border-[#C59B40] font-medium text-sm px-8 py-3 rounded-full shadow-xs transition transform hover:-translate-y-0.5">
                        Konsultasi Haji
                    </a>
                </div>
            </div>

        </div>
    </section>


    <!-- bagian layanan kami -->
    <section id="layanan-kami" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 space-y-10">
        
        <div class="space-y-2">
            <span class="text-[#8B6A2B] font-serif font-semibold tracking-[0.22em] uppercase text-xs block">BERSAMA ROZWIA</span>
            <h2 class="text-3xl sm:text-5xl font-bold text-[#0B3B24] font-serif">Layanan Kami</h2>
            <p class="text-stone-700 text-sm sm:text-base font-serif">Rasakan pengalaman ibadah yang nyaman dan berkesan bersama ROZWIA</p>
            <div class="w-12 h-1 bg-[#B78616]/70 rounded-full mt-3"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            
            <!-- Card 01 -->
            <div class="bg-[#FBF9F5] rounded-[24px] p-8 shadow-xs border border-stone-200/70 flex flex-col justify-between hover:shadow-md hover:-translate-y-1 transition duration-300 group">
                <div class="flex items-start gap-5">
                    <div class="w-12 h-12 flex items-center justify-center text-[#C96B00] shrink-0 group-hover:scale-110 transition">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <div class="space-y-2 font-serif">
                        <h3 class="text-xl sm:text-2xl font-bold text-[#0B3B24]">Pelayanan Ramah</h3>
                        <p class="text-stone-600 text-sm leading-relaxed">Pelayanan penuh perhatian untuk setiap jamaah.</p>
                    </div>
                </div>
                <div class="pt-6 flex justify-end"><span class="text-xs font-serif font-bold text-[#8B6A2B]">01</span></div>
            </div>

            <!-- Card 02 -->
            <div class="bg-[#FBF9F5] rounded-[24px] p-8 shadow-xs border border-stone-200/70 flex flex-col justify-between hover:shadow-md hover:-translate-y-1 transition duration-300 group">
                <div class="flex items-start gap-5">
                    <div class="w-12 h-12 flex items-center justify-center text-[#C96B00] shrink-0 group-hover:scale-110 transition">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="space-y-2 font-serif">
                        <h3 class="text-xl sm:text-2xl font-bold text-[#0B3B24]">Pendamping Jamaah</h3>
                        <p class="text-stone-600 text-sm leading-relaxed">Pendamping selama perjalanan ibadah dengan nyaman.</p>
                    </div>
                </div>
                <div class="pt-6 flex justify-end"><span class="text-xs font-serif font-bold text-[#8B6A2B]">02</span></div>
            </div>

            <!-- Card 03 -->
            <div class="bg-[#FBF9F5] rounded-[24px] p-8 shadow-xs border border-stone-200/70 flex flex-col justify-between hover:shadow-md hover:-translate-y-1 transition duration-300 group">
                <div class="flex items-start gap-5">
                    <div class="w-12 h-12 flex items-center justify-center text-[#C96B00] shrink-0 group-hover:scale-110 transition">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="space-y-2 font-serif">
                        <h3 class="text-xl sm:text-2xl font-bold text-[#0B3B24]">Jadwal Terencana</h3>
                        <p class="text-stone-600 text-sm leading-relaxed">Informasi keberangkatan teratur.</p>
                    </div>
                </div>
                <div class="pt-6 flex justify-end"><span class="text-xs font-serif font-bold text-[#8B6A2B]">03</span></div>
            </div>

            <!-- Card 04 -->
            <div class="bg-[#FBF9F5] rounded-[24px] p-8 shadow-xs border border-stone-200/70 flex flex-col justify-between hover:shadow-md hover:-translate-y-1 transition duration-300 group">
                <div class="flex items-start gap-5">
                    <div class="w-12 h-12 flex items-center justify-center text-[#C96B00] font-serif font-extrabold text-2xl shrink-0 group-hover:scale-110 transition">Rp</div>
                    <div class="space-y-2 font-serif">
                        <h3 class="text-xl sm:text-2xl font-bold text-[#0B3B24]">Biaya Transparan</h3>
                        <p class="text-stone-600 text-sm leading-relaxed">Informasi biaya fasilitas yang jelas.</p>
                    </div>
                </div>
                <div class="pt-6 flex justify-end"><span class="text-xs font-serif font-bold text-[#8B6A2B]">04</span></div>
            </div>

        </div>

        <div class="bg-[#0B3B24] text-[#FDFBF7] px-7 py-4 rounded-2xl shadow-sm font-serif text-xs sm:text-sm">
            <p class="font-medium">Lebih tenang mempersiapkan perjalanan, lebih fokus menjalankan ibadah.</p>
        </div>

    </section>


    <!-- bagian testimoni jamaah -->
    <section id="testimoni" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 space-y-12">
        
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">
            <div class="space-y-4 max-w-2xl">
                <span class="inline-block bg-[#E5D3B3] border border-amber-700/30 text-emerald-950 font-serif font-semibold text-xs sm:text-sm px-6 py-2 rounded-full shadow-2xs">
                    Testimoni Jamaah
                </span>
                <h2 class="text-3xl sm:text-5xl font-bold text-stone-950 font-serif leading-tight">
                    Kata Mereka yang Berangkat Bersama ROZWIA
                </h2>
                <p class="text-stone-700 text-sm sm:text-base font-serif">
                    Cerita dan pengalaman jamaah yang telah menjalani ibadah bersama kami.
                </p>
            </div>

            <div class="bg-white px-7 py-4 rounded-full shadow-sm border border-stone-200/70 flex items-center gap-5 self-start lg:self-end">
                <span class="text-2xl sm:text-3xl font-extrabold text-emerald-950 font-serif tracking-tight">4,9</span>
                <div class="space-y-0.5">
                    <div class="flex items-center gap-1 text-amber-400 text-base sm:text-lg">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="text-[11px] font-serif text-emerald-950 font-medium">Rating dan Ulasan dari Jamaah</p>
                </div>
            </div>
        </div>

        <div class="flex overflow-x-auto gap-6 pb-6 snap-x snap-mandatory scroll-smooth">
            <!-- Testimoni 1 -->
            <div class="min-w-[300px] sm:min-w-[340px] max-w-[340px] bg-[#FBF9F5] rounded-2xl p-7 shadow-xs border border-stone-200/70 flex flex-col justify-between snap-start">
                <div class="space-y-4">
                    <div class="flex items-center gap-1 text-amber-400 text-xl"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                    <p class="text-stone-800 text-xs sm:text-sm font-serif leading-relaxed">Pembimbing dan tim yang sabar mendampingi, terutama orang tua atau jamaah lansia. Ini biasanya yang paling menenangkan calon jamaah.</p>
                </div>
                <div class="pt-6 mt-6 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-full bg-amber-100 border border-amber-300 flex items-center justify-center text-lg shrink-0">👨🏻</div>
                    <div class="text-xs font-serif"><p class="font-bold text-stone-900">Rafid</p><p class="text-stone-600">Bogor . Haji (2022)</p></div>
                </div>
            </div>

            <!-- Testimoni 2 -->
            <div class="min-w-[300px] sm:min-w-[340px] max-w-[340px] bg-[#FBF9F5] rounded-2xl p-7 shadow-xs border border-stone-200/70 flex flex-col justify-between snap-start">
                <div class="space-y-4">
                    <div class="flex items-center gap-1 text-amber-400 text-xl"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                    <p class="text-stone-800 text-xs sm:text-sm font-serif leading-relaxed">Hotel, jarak ke Masjidil Haram, makanan dan transportasi selama disana sangat terjamin. Calon jamaah tidak perlu khawatir soal kenyamanan.</p>
                </div>
                <div class="pt-6 mt-6 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-full bg-emerald-100 border border-emerald-300 flex items-center justify-center text-lg shrink-0">🧔🏻</div>
                    <div class="text-xs font-serif"><p class="font-bold text-stone-900">Raid</p><p class="text-stone-600">Bogor . Umrah (2020)</p></div>
                </div>
            </div>

            <!-- Testimoni 3 -->
            <div class="min-w-[300px] sm:min-w-[340px] max-w-[340px] bg-[#FBF9F5] rounded-2xl p-7 shadow-xs border border-stone-200/70 flex flex-col justify-between snap-start">
                <div class="space-y-4">
                    <div class="flex items-center gap-1 text-amber-400 text-xl"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                    <p class="text-stone-800 text-xs sm:text-sm font-serif leading-relaxed">Perjalanan berjalan sesuai rencana, informasi jelas, dan tidak ada kebingungan saat di tengah jalan. Semuanya terorganisir dengan sangat baik.</p>
                </div>
                <div class="pt-6 mt-6 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-full bg-rose-100 border border-rose-300 flex items-center justify-center text-lg shrink-0">🧕🏻</div>
                    <div class="text-xs font-serif"><p class="font-bold text-stone-900">Rinzani</p><p class="text-stone-600">Jakarta . Umrah (2023)</p></div>
                </div>
            </div>

            <!-- Testimoni 4 -->
            <div class="min-w-[300px] sm:min-w-[340px] max-w-[340px] bg-[#FBF9F5] rounded-2xl p-7 shadow-xs border border-stone-200/70 flex flex-col justify-between snap-start">
                <div class="space-y-4">
                    <div class="flex items-center gap-1 text-amber-400 text-xl"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                    <p class="text-stone-800 text-xs sm:text-sm font-serif leading-relaxed">Pengurusan visa dan dokumen dari awal keberangkatan sampai pulang sangat dibantu penuh oleh tim ROZWIA. Ibadah terasa jauh lebih khusyuk dan tenang.</p>
                </div>
                <div class="pt-6 mt-6 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-full bg-amber-100 border border-amber-300 flex items-center justify-center text-lg shrink-0">👩🏻‍💼</div>
                    <div class="text-xs font-serif"><p class="font-bold text-stone-900">Olif</p><p class="text-stone-600">Bandung . Umrah (2024)</p></div>
                </div>
            </div>
        </div>

    </section>


    <!-- banner ajakan daftar -->
    <section id="cta-bawah" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="bg-[#0B3B24] rounded-[32px] p-8 sm:p-14 shadow-xl border border-emerald-900 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8">
            
            <div class="space-y-3 max-w-xl">
                <h2 class="text-2xl sm:text-4xl font-serif font-bold text-white tracking-tight">
                    Siap Memulai Perjalanan Menuju Baitullah?
                </h2>
                <p class="text-emerald-100/85 text-sm sm:text-base font-serif leading-relaxed">
                    Jangan ragu untuk mengambil langkah pertama. Daftarkan diri Anda atau konsultasi kebutuhan perjalanan Umrah bersama tim ROZWIA.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 shrink-0">
                <a href="{{ route('paket.umroh') }}" class="bg-[#F59100] hover:bg-[#e08500] text-[#0B3B24] font-serif font-bold text-sm px-7 py-3.5 rounded-2xl shadow-md transition transform hover:-translate-y-0.5">
                    Daftar Paket
                </a>
                <a href="https://wa.me/6285122978237?text=Assalamualaikum%20ROZWIA,%20saya%20ingin%20bertanya%20mengenai%20jadwal%20keberangkatan%20dan%20paket%20perjalanan." target="_blank" class="bg-emerald-800 hover:bg-emerald-700 text-white font-serif font-bold text-sm px-7 py-3.5 rounded-2xl border border-emerald-600/50 shadow-md transition transform hover:-translate-y-0.5">
                    Chat Via WhatsApp
                </a>
            </div>

        </div>
    </section>

</div>

@endsection

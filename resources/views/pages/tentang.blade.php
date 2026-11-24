@extends('layouts.app')

@section('title', 'Tentang Kami - ROZWIA Islamic Travel')

@section('content')
@php
    $companyProfile = \App\Helpers\RozwiaHelper::companyProfile();
    $waDigits = preg_replace('/[^0-9]/', '', $companyProfile['telepon'] ?? '');
    if (str_starts_with($waDigits, '0')) {
        $waDigits = '62' . substr($waDigits, 1);
    }
    $waNumber = $waDigits ?: '6285122978237';
@endphp

<!-- wrapper halaman -->
<div class="w-full bg-[#F4EFE6] min-h-screen pt-28 sm:pt-32 pb-24">

    <!-- BANNER HERO HEADER -->
    <div class="relative w-full bg-[#0B3B24] overflow-hidden shadow-md py-16 sm:py-20 px-4 sm:px-6 lg:px-8 mb-16">
        <div class="absolute inset-0 opacity-20 bg-cover bg-center" style="background-image: url('{{ asset('images/about us.jpg') }}')"></div>

        <div class="relative z-10 max-w-4xl mx-auto text-center space-y-4">
            <p class="text-xs text-amber-300 font-serif tracking-widest uppercase">
                <a href="{{ route('landing') }}" class="hover:underline">Beranda</a> &gt; <span class="text-white font-medium">Tentang Kami</span>
            </p>
            <h1 class="text-3xl sm:text-5xl font-bold text-white font-serif tracking-tight">
                Mengenal Lebih Dekat <br class="hidden sm:inline"> ROZWIA Islamic Travel
            </h1>
            <p class="text-emerald-100/90 text-sm sm:text-base font-serif max-w-2xl mx-auto leading-relaxed">
                Sahabat terpercaya dan profesional dalam mewujudkan perjalanan ibadah Umroh dan Haji khusus yang amanah, khusyuk, dan sesuai sunnah.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-20">

        <!-- latar belakang & legalitas -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            <!-- Kolom Teks Latar Belakang -->
            <div class="lg:col-span-7 space-y-6">
                <div class="space-y-2">
                    <span class="text-amber-700 font-bold uppercase tracking-wider text-xs font-serif">Latar Belakang &amp; Legalitas</span>
                    <h2 class="text-2xl sm:text-4xl font-bold text-[#0B3B24] font-serif tracking-tight">
                        Penyelenggara Perjalanan Ibadah Suci Berizin Resmi Kemenag RI
                    </h2>
                </div>

                <div class="space-y-4 text-stone-700 text-xs sm:text-sm font-serif leading-relaxed bg-white p-6 sm:p-8 rounded-[28px] shadow-sm border border-stone-200/80">
                    <p>
                        <strong>PT ROZWIA Islamic Travel</strong> lahir dari panggilan mulia untuk memberikan kepastian, kenyamanan, dan rasa aman bagi umat Muslim di Indonesia yang rindu menunaikan ibadah ke Baitullah. Kami memahami bahwa perjalanan ke Tanah Suci adalah momen spiritual yang sangat sakral dalam hidup seorang Muslim.
                    </p>
                    <p>
                        Oleh karena itu, kami berkomitmen menghadirkan tata kelola travel yang transparan, akuntabel, dan profesional. Dengan dukungan perizinan resmi sebagai Penyelenggara Perjalanan Ibadah Umrah (PPIU) dan Penyelenggara Ibadah Haji Khusus (PIHK) di bawah pengawasan <strong>Kementerian Agama Republik Indonesia</strong>, setiap langkah perjalanan jamaah dijamin aman secara hukum dan regulasi.
                    </p>
                </div>

                <!-- Poin Statistik Resmi -->
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs space-y-1">
                        <span class="block text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif">100%</span>
                        <span class="text-xs text-stone-600 font-serif font-medium">Berizin Resmi Kemenag RI</span>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs space-y-1">
                        <span class="block text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif">Bintang 5</span>
                        <span class="text-xs text-stone-600 font-serif font-medium">Akomodasi Dekat Haram</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Gambar / Visual Estetik -->
            <div class="lg:col-span-5">
                <div class="relative h-[420px] rounded-[32px] overflow-hidden shadow-xl border-4 border-white">
                    <img src="{{ asset('images/Masjid nabawi.jpg') }}" alt="Masjid Nabawi Madinah - ROZWIA Islamic Travel" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/70 via-transparent to-transparent flex items-end p-8">
                        <p class="text-white text-xs sm:text-sm font-serif italic">
                            "Melayani Tamu Allah dengan sepenuh hati, membimbing ibadah sesuai sunnah."
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- layanan kami -->
        <div class="space-y-8">
            <div class="text-center space-y-2">
                <span class="text-amber-700 font-bold uppercase tracking-wider text-xs font-serif">Program Unggulan</span>
                <h3 class="text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif">Layanan Perjalanan Kami</h3>
                <div class="w-16 h-1 bg-amber-500 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Paket Haji -->
                <div class="bg-[#0B3B24] rounded-3xl p-8 sm:p-10 shadow-lg text-white flex flex-col justify-between space-y-6 border border-emerald-900">
                    <div class="space-y-3">
                        <span class="inline-block bg-amber-400 text-emerald-950 text-[10px] font-bold font-serif px-3 py-1 rounded-full uppercase tracking-wider">Eksklusif &amp; Resmi</span>
                        <h4 class="text-2xl font-bold font-serif text-amber-300">Paket Haji Khusus &amp; Furoda</h4>
                        <p class="text-emerald-100/90 text-xs sm:text-sm font-serif leading-relaxed">
                            Program haji dengan kepastian keberangkatan tanpa antri panjang, didukung maktab VIP Armuzna, tenda ber-AC, serta akomodasi terbaik di dekat Masjidil Haram.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('paket.haji') }}" class="inline-block bg-[#E5D3B3] hover:bg-[#d8c4a1] text-emerald-950 font-serif font-bold text-xs px-6 py-3 rounded-full shadow-md transition">
                            Lihat Paket Haji
                        </a>
                    </div>
                </div>

                <!-- Paket Umrah -->
                <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-sm text-stone-800 flex flex-col justify-between space-y-6 border border-stone-200/80">
                    <div class="space-y-3">
                        <span class="inline-block bg-emerald-100 text-emerald-900 text-[10px] font-bold font-serif px-3 py-1 rounded-full uppercase tracking-wider">Pilihan Fleksibel</span>
                        <h4 class="text-2xl font-bold font-serif text-[#0B3B24]">Paket Umrah Reguler &amp; Plus</h4>
                        <p class="text-stone-600 text-xs sm:text-sm font-serif leading-relaxed">
                            Berbagai pilihan durasi (9, 12, hingga 15 hari) serta program Umrah Plus wisata religi (Turki, Dubai, Kairo) dengan fasilitas hotel bintang 4 dan 5.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('paket.umroh') }}" class="inline-block bg-[#0B3B24] hover:bg-emerald-900 text-white font-serif font-semibold text-xs px-6 py-3 rounded-full shadow-sm transition">
                            Lihat Paket Umrah
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- keunggulan layanan -->
        <div class="space-y-8">
            <div class="text-center space-y-2">
                <span class="text-amber-700 font-bold uppercase tracking-wider text-xs font-serif">Mengapa Memilih Kami</span>
                <h3 class="text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif">Keunggulan Layanan ROZWIA</h3>
                <div class="w-16 h-1 bg-amber-500 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- 1 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200/85 space-y-4 relative flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#0B3B24] flex items-center justify-center font-bold">
                            <svg class="w-6 h-6 text-[#0B3B24]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <h4 class="font-bold text-[#0B3B24] font-serif text-base">Pelayanan Amanah</h4>
                        <p class="text-xs text-stone-600 font-serif leading-relaxed">Komitmen penuh memberikan pelayanan terbaik dan transparan bagi setiap jamaah.</p>
                    </div>
                    <span class="absolute bottom-4 right-5 text-xs font-bold text-stone-300 font-serif">01</span>
                </div>

                <!-- 2 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200/85 space-y-4 relative flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#0B3B24] flex items-center justify-center font-bold">
                            <svg class="w-6 h-6 text-[#0B3B24]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h4 class="font-bold text-[#0B3B24] font-serif text-base">Pembimbing Berpengalaman</h4>
                        <p class="text-xs text-stone-600 font-serif leading-relaxed">Didampingi muthawif dan ustadz kompeten sesuai tuntunan sunnah Rasulullah SAW.</p>
                    </div>
                    <span class="absolute bottom-4 right-5 text-xs font-bold text-stone-300 font-serif">02</span>
                </div>

                <!-- 3 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200/85 space-y-4 relative flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#0B3B24] flex items-center justify-center font-bold">
                            <svg class="w-6 h-6 text-[#0B3B24]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h4 class="font-bold text-[#0B3B24] font-serif text-base">Jadwal Pasti</h4>
                        <p class="text-xs text-stone-600 font-serif leading-relaxed">Kepastian tanggal keberangkatan dan maskapai penerbangan berstandar internasional.</p>
                    </div>
                    <span class="absolute bottom-4 right-5 text-xs font-bold text-stone-300 font-serif">03</span>
                </div>

                <!-- 4 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200/85 space-y-4 relative flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#0B3B24] flex items-center justify-center font-bold">
                            <svg class="w-6 h-6 text-[#0B3B24]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="font-bold text-[#0B3B24] font-serif text-base">Biaya Transparan</h4>
                        <p class="text-xs text-stone-600 font-serif leading-relaxed">Rincian biaya jelas tanpa ada biaya tersembunyi selama proses perjalanan.</p>
                    </div>
                    <span class="absolute bottom-4 right-5 text-xs font-bold text-stone-300 font-serif">04</span>
                </div>
            </div>

            <!-- Quote banner kecil -->
            <div class="bg-[#0B3B24] text-white text-center py-4 px-6 rounded-2xl font-serif text-xs sm:text-sm tracking-wide shadow-sm">
                Lebih tenang mempersiapkan perjalanan, lebih fokus menjalankan ibadah.
            </div>
        </div>

        <!-- alur perjalanan ibadah -->
        <div class="space-y-8">
            <div class="text-center space-y-2">
                <span class="text-amber-700 font-bold uppercase tracking-wider text-xs font-serif">Alur Pelayanan</span>
                <h3 class="text-2xl sm:text-4xl font-bold text-[#0B3B24] font-serif">
                    Perjalanan Ibadah Anda <br>
                    <span class="text-amber-700 italic">Selangkah demi Langkah</span>
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4">
                @php
                    $steps = [
                        ['num' => '1', 'title' => 'Pendaftaran', 'desc' => 'Pilih paket dan isi data diri jamaah.'],
                        ['num' => '2', 'title' => 'Persiapan Dokumen', 'desc' => 'Pengurusan paspor, visa, dan perlengkapan.'],
                        ['num' => '3', 'title' => 'Manasik & Pembekalan', 'desc' => 'Bimbingan tata cara ibadah sebelum berangkat.'],
                        ['num' => '4', 'title' => 'Keberangkatan', 'desc' => 'Penerbangan menuju Tanah Suci.'],
                        ['num' => '5', 'title' => 'Tiba di Tanah Suci', 'desc' => 'Penyambutan dan check-in hotel di Makkah/Madinah.'],
                        ['num' => '6', 'title' => 'Pelaksanaan Ibadah', 'desc' => 'Menjalankan umrah/haji dipandu muthawif.'],
                        ['num' => '7', 'title' => 'Perjalanan Pulang', 'desc' => 'Kepulangan jamaah kembali ke tanah air.'],
                        ['num' => '8', 'title' => 'Tiba di Indonesia', 'desc' => 'Jamaah berkumpul kembali dengan keluarga tercinta.'],
                    ];
                @endphp

                @foreach($steps as $step)
                <div class="bg-white rounded-2xl p-5 shadow-xs border border-stone-200/80 flex items-center gap-4 transition hover:border-emerald-300">
                    <span class="w-10 h-10 rounded-full bg-[#0B3B24] text-amber-300 text-sm font-bold flex items-center justify-center shrink-0 font-serif shadow-sm">
                        {{ $step['num'] }}
                    </span>
                    <div class="space-y-0.5">
                        <h4 class="font-bold text-[#0B3B24] text-xs font-serif">{{ $step['title'] }}</h4>
                        <p class="text-[11px] text-stone-600 font-serif leading-tight">{{ $step['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- kontak & peta lokasi -->
        <div class="bg-white rounded-[36px] p-8 sm:p-12 shadow-sm border border-stone-200/80 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif">Hubungi Kami</h3>
                    <p class="text-stone-600 text-xs sm:text-sm font-serif leading-relaxed">
                        Ada pertanyaan seputar paket, jadwal keberangkatan, atau pendaftaran? Tim ROZWIA siap membantu memberikan informasi dan menjawab kebutuhan perjalanan ibadah Anda.
                    </p>
                </div>

                <div class="flex flex-wrap gap-4">
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="bg-emerald-50 hover:bg-emerald-100 text-[#0B3B24] border border-emerald-200 px-5 py-3 rounded-2xl font-serif text-xs font-bold flex items-center gap-2.5 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 256 258" aria-hidden="true" class="shrink-0">
                            <path fill="#1faf38" d="M5.463 127.456c-.006 21.677 5.658 42.843 16.428 61.499L4.433 252.697l65.232-17.104a123 123 0 0 0 58.8 14.97h.054c67.815 0 123.018-55.183 123.047-123.01c.013-32.867-12.775-63.773-36.009-87.025c-23.23-23.25-54.125-36.061-87.043-36.076c-67.823 0-123.022 55.18-123.05 123.004" />
                            <path fill="#fff" d="M96.678 74.148c-2.386-5.303-4.897-5.41-7.166-5.503c-1.858-.08-3.982-.074-6.104-.074c-2.124 0-5.575.799-8.492 3.984c-2.92 3.188-11.148 10.892-11.148 26.561s11.413 30.813 13.004 32.94c1.593 2.123 22.033 35.307 54.405 48.073c26.904 10.609 32.379 8.499 38.218 7.967c5.84-.53 18.844-7.702 21.497-15.139c2.655-7.436 2.655-13.81 1.859-15.142c-.796-1.327-2.92-2.124-6.105-3.716s-18.844-9.298-21.763-10.361c-2.92-1.062-5.043-1.592-7.167 1.597c-2.124 3.184-8.223 10.356-10.082 12.48c-1.857 2.129-3.716 2.394-6.9.801c-3.187-1.598-13.444-4.957-25.613-15.806c-9.468-8.442-15.86-18.867-17.718-22.056c-1.858-3.184-.199-4.91 1.398-6.497c1.431-1.427 3.186-3.719 4.78-5.578c1.588-1.86 2.118-3.187 3.18-5.311c1.063-2.126.531-3.986-.264-5.579c-.798-1.593-6.987-17.343-9.819-23.64" />
                        </svg>
                        <span class="font-sans">{{ $companyProfile['telepon'] }}</span>
                    </a>
                    <a href="mailto:{{ $companyProfile['email'] }}" class="bg-emerald-50 hover:bg-emerald-100 text-[#0B3B24] border border-emerald-200 px-5 py-3 rounded-2xl font-serif text-xs font-bold flex items-center gap-2.5 transition">
                        <x-icon name="mail" class="w-4 h-4 text-[#0B3B24]" />
                        <span class="font-sans">{{ $companyProfile['email'] }}</span>
                    </a>
                </div>

                <div class="space-y-2 pt-2 border-t border-stone-200">
                    <h4 class="font-bold text-[#0B3B24] text-xs font-serif uppercase tracking-wider">Kantor Pusat:</h4>
                    <p class="text-xs text-stone-600 font-serif leading-relaxed">
                        {{ $companyProfile['alamat'] }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="{{ route('faq') }}" class="bg-stone-100 hover:bg-stone-200 text-stone-800 font-serif font-bold text-xs px-6 py-3 rounded-2xl transition">
                        Lihat Pertanyaan Umum
                    </a>
                    <a href="https://wa.me/{{ $waNumber }}?text=Assalamualaikum%20ROZWIA,%20saya%20ingin%20berkonsultasi%20mengenai%20perjalanan%20ibadah." target="_blank" class="bg-[#25D366] hover:bg-[#20ba5a] text-white font-serif font-bold text-xs px-6 py-3 rounded-2xl shadow-md transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 256 258" aria-hidden="true" class="shrink-0">
                            <path fill="#fff" d="M128 0C57.3 0 0 57.3 0 128c0 22.6 5.9 44.6 17.1 64L0 256l65.6-17.2C84.4 249.4 106 255 128 255c70.7 0 128-57.3 128-127S198.7 0 128 0zm62.9 180.2c-2.6 7.4-15.2 14.1-21.1 15-5.4.8-12.4 1.1-20-1.3-4.6-1.5-10.5-3.4-18.1-6.7-31.9-13.8-52.7-46-54.3-48.1-1.6-2.1-13-17.3-13-33s8.2-23.4 11.1-26.6c2.9-3.2 6.4-4 8.5-4h6.1c2 0 4.6-.8 7.2 5.5 2.6 6.3 9 22 9.8 23.6.8 1.6 1.3 3.5.3 5.6-1 2.1-1.6 3.5-3.2 5.3-1.6 1.9-3.3 4.1-4.8 5.6-1.6 1.6-3.3 3.3-1.4 6.5 1.9 3.2 8.3 13.6 17.7 22 12.2 10.9 22.4 14.2 25.6 15.8 3.2 1.6 5 1.3 6.9-.8 1.9-2.1 8-9.3 10.1-12.5 2.1-3.2 4.2-2.7 7.2-1.6 3 1.1 18.8 8.9 22 10.5 3.2 1.6 5.3 2.4 6.1 3.7.8 1.4.8 7.7-1.9 15.1z" />
                        </svg>
                        <span>Chat Via WhatsApp</span>
                    </a>
                </div>
            </div>

            <!-- Google Maps Embed -->
            <div class="lg:col-span-6">
                <div class="relative h-72 sm:h-80 rounded-2xl overflow-hidden shadow-inner border border-stone-300 bg-stone-100">
                    <iframe
                        title="Lokasi Kantor ROZWIA"
                        src="https://www.google.com/maps?q={{ urlencode($companyProfile['alamat']) }}&output=embed"
                        class="w-full h-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

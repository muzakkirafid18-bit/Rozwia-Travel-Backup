<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ROZWIA - Islamic Travel & Tour')</title>
    @include('partials.favicon')
    <!-- styling vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- alpinejs -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="bg-[#FDFBF7] font-sans text-stone-800 antialiased selection:bg-emerald-900 selection:text-white min-h-screen flex flex-col justify-between"
      x-data="{ loginOpen: {{ $errors->has('email') || $errors->has('password') || request()->has('login') ? 'true' : 'false' }}, mobileMenuOpen: false }"
      x-effect="document.body.classList.toggle('overflow-hidden', loginOpen)">

    <!-- navbar utama -->
    @php
        $landingUrl = rtrim(request()->getSchemeAndHttpHost() . request()->getBaseUrl(), '/') . '/';
    @endphp
    <header class="fixed top-0 z-50 w-full bg-[#F4EBDD] px-4 pb-4 pt-5 sm:px-8">
        <div class="mx-auto flex max-w-7xl items-center justify-between rounded-[22px] border border-emerald-900/50 bg-[#063B25] px-4 sm:px-6 py-3 shadow-lg">
            <!-- Logo Rozwia -->
            <div class="flex items-center gap-4">
                <a href="{{ $landingUrl }}" class="flex items-center gap-3 text-white font-serif font-bold tracking-wider group">
                    <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA Logo" class="h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-105">
                    <div class="leading-tight">
                        <span class="block tracking-widest text-lg">ROZWIA</span>
                        <span class="block text-[9px] tracking-widest text-[#D4B996] font-sans font-light">~ISLAMIC TRAVEL~</span>
                    </div>
                </a>
                <span class="hidden md:block h-7 w-px bg-emerald-700/70 ml-2"></span>
            </div>

            <!-- Menu Navigasi Desktop -->
            <nav class="hidden md:flex items-center space-x-2 text-sm font-serif text-emerald-100">
                <a href="{{ $landingUrl }}" class="px-4 py-1.5 rounded-lg transition {{ request()->routeIs('landing') ? 'border border-[#B78616]/70 bg-emerald-900/40 text-white' : 'hover:bg-emerald-900/40 hover:text-white' }}">Beranda</a>
                <a href="{{ route('tentang.kami') }}" class="px-4 py-1.5 rounded-lg transition {{ request()->routeIs('tentang.kami') ? 'border border-[#B78616]/70 bg-emerald-900/40 text-white' : 'hover:bg-emerald-900/40 hover:text-white' }}">Tentang Kami</a>
                <a href="{{ route('paket.haji') }}" class="px-4 py-1.5 rounded-lg transition {{ request()->routeIs('paket.haji') ? 'border border-[#B78616]/70 bg-emerald-900/40 text-white' : 'hover:bg-emerald-900/40 hover:text-white' }}">Paket Haji</a>
                <a href="{{ route('paket.umroh') }}" class="px-4 py-1.5 rounded-lg transition {{ request()->routeIs('paket.umroh') ? 'border border-[#B78616]/70 bg-emerald-900/40 text-white' : 'hover:bg-emerald-900/40 hover:text-white' }}">Paket Umrah</a>
                <a href="{{ route('faq') }}" class="px-4 py-1.5 rounded-lg transition {{ request()->routeIs('faq') ? 'border border-[#B78616]/70 bg-emerald-900/40 text-white' : 'hover:bg-emerald-900/40 hover:text-white' }}">FAQ</a>
            </nav>

            <!-- Tombol Navigasi Auth & Hamburger Mobile -->
            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    @php
                        $roleUrl   = route(auth()->user()->dashboardRouteName());
                        $roleBadge = auth()->user()->roleLabel();
                    @endphp
                    <a href="{{ $roleUrl }}" class="hidden sm:flex items-center gap-2 bg-emerald-800 hover:bg-emerald-700 text-amber-200 border border-emerald-600/60 px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm font-serif">
                        <x-icon name="person" class="w-4 h-4" /> {{ auth()->user()->name }} ({{ $roleBadge }})
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 bg-red-600/80 hover:bg-red-500 text-white px-3.5 py-2 rounded-xl text-xs font-semibold transition cursor-pointer shadow-sm">
                            <x-icon name="logout" class="w-4 h-4" /> Keluar
                        </button>
                    </form>
                @else
                    <button type="button" @click="loginOpen = true" class="flex items-center gap-1.5 sm:gap-2 rounded-lg border border-[#D1A728] px-3.5 sm:px-5 py-2 text-xs sm:text-sm font-bold text-[#E7C878] shadow-sm transition hover:bg-emerald-900 font-serif cursor-pointer">
                        <x-icon name="person" class="w-4 h-4" /> Login
                    </button>
                @endauth

                <button type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden inline-flex items-center justify-center rounded-xl border border-emerald-700/70 bg-emerald-900/50 p-2 text-emerald-100 hover:bg-emerald-800 transition cursor-pointer"
                        aria-label="Buka menu navigasi">
                    <span x-show="!mobileMenuOpen"><x-icon name="menu" class="w-5 h-5" /></span>
                    <span x-show="mobileMenuOpen" x-cloak><x-icon name="close" class="w-5 h-5" /></span>
                </button>
            </div>
        </div>

        <!-- menu mobile -->
        <div x-show="mobileMenuOpen"
             x-transition
             @click.outside="mobileMenuOpen = false"
             style="display: none;"
             class="md:hidden mx-auto mt-2 max-w-7xl rounded-[20px] border border-emerald-900/50 bg-[#063B25] p-4 shadow-xl space-y-1.5 font-serif text-sm text-emerald-100">
            <a href="{{ $landingUrl }}" @click="mobileMenuOpen = false" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('landing') ? 'bg-emerald-900/70 border border-[#B78616]/60 text-white font-bold' : 'hover:bg-emerald-900/40' }}">Beranda</a>
            <a href="{{ route('tentang.kami') }}" @click="mobileMenuOpen = false" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('tentang.kami') ? 'bg-emerald-900/70 border border-[#B78616]/60 text-white font-bold' : 'hover:bg-emerald-900/40' }}">Tentang Kami</a>
            <a href="{{ route('paket.haji') }}" @click="mobileMenuOpen = false" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('paket.haji') ? 'bg-emerald-900/70 border border-[#B78616]/60 text-white font-bold' : 'hover:bg-emerald-900/40' }}">Paket Haji</a>
            <a href="{{ route('paket.umroh') }}" @click="mobileMenuOpen = false" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('paket.umroh') ? 'bg-emerald-900/70 border border-[#B78616]/60 text-white font-bold' : 'hover:bg-emerald-900/40' }}">Paket Umrah</a>
            <a href="{{ route('faq') }}" @click="mobileMenuOpen = false" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('faq') ? 'bg-emerald-900/70 border border-[#B78616]/60 text-white font-bold' : 'hover:bg-emerald-900/40' }}">FAQ</a>

            @auth
                <div class="pt-2 mt-2 border-t border-emerald-800/80 flex flex-col gap-2 sm:hidden">
                    <a href="{{ $roleUrl }}" class="flex items-center justify-center gap-2 bg-emerald-800 text-amber-200 border border-emerald-600/60 px-4 py-2.5 rounded-xl text-xs font-bold">
                        <x-icon name="person" class="w-4 h-4" /> {{ auth()->user()->name }} ({{ $roleBadge }})
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-1.5 bg-red-600/80 text-white px-4 py-2.5 rounded-xl text-xs font-semibold cursor-pointer">
                            <x-icon name="logout" class="w-4 h-4" /> Keluar
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <!-- konten utama -->
    <main class="w-full flex-grow">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-[#0B3B24] text-[#E8DFD1] pt-16 pb-10 border-t border-emerald-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 pb-12">
                
                <!-- Kolom 1: Logo & Deskripsi -->
                <div class="lg:col-span-4 space-y-5">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA Logo" class="h-11 w-auto object-contain">
                        <div class="leading-tight font-serif">
                            <span class="block tracking-widest text-xl font-bold text-white">ROZWIA</span>
                            <span class="block text-[10px] tracking-widest text-[#D4B996]">~ISLAMIC TRAVEL~</span>
                        </div>
                    </div>
                    <p class="text-xs sm:text-sm text-[#E8DFD1]/85 font-serif leading-relaxed max-w-xs">
                        Menemani langkah Anda menuju Baitullah dengan layanan yang amanah, nyaman, dan penuh keberkahan.
                    </p>
                </div>

                <!-- Kolom 2: Paket -->
                <div class="lg:col-span-3 space-y-4 font-serif">
                    <h4 class="font-bold text-[#D4B996] text-base tracking-wide">Paket</h4>
                    <ul class="space-y-2.5 text-sm text-[#E8DFD1]/85">
                        <li><a href="{{ route('paket.haji') }}" class="hover:text-white transition">&rarr; Paket Haji</a></li>
                        <li><a href="{{ route('paket.umroh') }}" class="hover:text-white transition">&rarr; Paket Umroh</a></li>
                        <li><a href="{{ route('paket.umroh') }}" class="hover:text-white transition">&rarr; Jadwal Keberangkatan</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Perusahaan -->
                <div class="lg:col-span-2 space-y-4 font-serif">
                    <h4 class="font-bold text-[#D4B996] text-base tracking-wide">Perusahaan</h4>
                    <ul class="space-y-2.5 text-sm text-[#E8DFD1]/85">
                        <li><a href="{{ route('tentang.kami') }}" class="hover:text-white transition">&rarr; Tentang Kami</a></li>
                        <li><a href="{{ route('landing') }}#testimoni" class="hover:text-white transition">&rarr; Testimoni</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-white transition">&rarr; Pertanyaan Umum</a></li>
                    </ul>
                </div>

                <!-- Kolom 4: Kontak -->
                @php
                    $companyProfile = \App\Helpers\RozwiaHelper::companyProfile();
                    $waDigits = preg_replace('/[^0-9]/', '', $companyProfile['telepon'] ?? '');
                    if (str_starts_with($waDigits, '0')) {
                        $waDigits = '62' . substr($waDigits, 1);
                    }
                @endphp
                <div class="lg:col-span-3 space-y-4 font-serif">
                    <h4 class="font-bold text-[#D4B996] text-base tracking-wide">Kontak</h4>
                    <div class="text-xs sm:text-sm text-[#E8DFD1]/85 space-y-1.5 leading-relaxed">
                        <p class="font-semibold text-white">Kantor Pusat:</p>
                        <p>{{ $companyProfile['alamat'] }}</p>
                    </div>
                    <div class="pt-2 space-y-3 text-sm">
                        <a href="https://wa.me/{{ $waDigits ?: '6285122978237' }}" target="_blank" class="flex items-center gap-3 hover:text-white transition">
                            <span class="w-7 h-7 rounded-full bg-emerald-800 text-emerald-300 flex items-center justify-center">
                                <x-icon name="call" class="w-3.5 h-3.5" />
                            </span>
                            <span class="font-sans">{{ $companyProfile['telepon'] }}</span>
                        </a>
                        <a href="mailto:{{ $companyProfile['email'] }}" class="flex items-center gap-3 hover:text-white transition">
                            <span class="w-7 h-7 rounded-full bg-amber-500 text-emerald-950 flex items-center justify-center">
                                <x-icon name="mail" class="w-3.5 h-3.5" />
                            </span>
                            <span class="font-sans">{{ $companyProfile['email'] }}</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Garis Pemisah & Copyright -->
            <div class="pt-6 border-t border-[#E8DFD1]/20 flex flex-col sm:flex-row items-center justify-between text-xs text-[#E8DFD1]/70 font-serif gap-4">
                <p>&copy; 2026 {{ $companyProfile['nama_perusahaan'] }}</p>
                <p>{{ $companyProfile['no_izin'] }}</p>
            </div>
        </div>
    </footer>

    <!-- modal login -->
    @guest
    <div x-show="loginOpen" 
         x-cloak
         @keydown.escape.window="loginOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 sm:p-6 overflow-y-auto"
         @click.self="loginOpen = false">
        
        <div x-show="loginOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative w-full max-w-4xl bg-white rounded-[32px] shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 border border-stone-200 my-8">
            
            <!-- tombol tutup modal -->
            <button @click="loginOpen = false" type="button" class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition font-bold cursor-pointer shadow-2xs">
                ✕
            </button>

            <!-- banner kiri modal -->
            <div class="hidden md:flex md:col-span-5 relative bg-[#0B3B24] p-8 flex-col justify-between overflow-hidden">
                <div class="absolute inset-0 opacity-25 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&q=80&w=800')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#0B3B24] via-[#0B3B24]/85 to-transparent"></div>

                <div class="relative z-10 space-y-2">
                    <span class="text-[10px] text-amber-300 font-serif uppercase tracking-widest font-bold">ROZWIA Islamic Travel</span>
                </div>

                <div class="relative z-10 space-y-4 py-8">
                    <h3 class="text-2xl sm:text-3xl font-bold font-serif text-white leading-snug tracking-tight">
                        Dari Niat <br>
                        menjadi <br>
                        <span class="text-amber-300">Perjalanan Nyata</span>
                    </h3>
                    <p class="text-emerald-100/80 text-xs font-serif leading-relaxed">
                        Masuk ke sistem manajemen ROZWIA untuk mengelola pendaftaran, keuangan dan monitoring perjalanan ibadah.
                    </p>
                </div>

                <div class="relative z-10 text-[10px] text-emerald-200/60 font-serif">
                    © 2026 ROZWIA Islamic Travel
                </div>
            </div>

            <!-- form login -->
            <div class="md:col-span-7 p-6 sm:p-10 flex flex-col justify-center bg-white space-y-5">
                
                <div class="text-center md:text-left space-y-1">
                    <span class="text-[10px] font-bold tracking-widest text-amber-800 uppercase font-serif">Akses Sistem</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif">Masuk ke Akun Anda</h2>
                </div>

                @if ($errors->any())
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-sans">
                    {{ $errors->first() }}
                </div>
                @endif

                <!-- Form Login -->
                <form action="{{ route('login.store') }}" method="POST" class="space-y-4 font-serif">
                    @csrf

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700">Email</label>
                        <input type="text" name="email" value="{{ old('email') }}" placeholder="nama@gmail.com" class="w-full px-4 py-3 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-[#0B3B24] text-xs font-sans" required autofocus>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5" x-data="{ showModalPassword: false }">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-700">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-[#0B3B24] hover:underline font-sans">Lupa Password?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <input :type="showModalPassword ? 'text' : 'password'" name="password" placeholder="••••••••" class="w-full px-4 py-3 pr-11 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-[#0B3B24] text-xs font-sans" required>
                            <button type="button"
                                    @click="showModalPassword = !showModalPassword"
                                    class="absolute inset-y-0 right-3 flex items-center text-stone-500 hover:text-[#0B3B24] cursor-pointer"
                                    title="Lihat / Sembunyikan Password"
                                    aria-label="Lihat atau sembunyikan password">
                                <span x-show="!showModalPassword"><x-icon name="eye" class="w-4 h-4" /></span>
                                <span x-show="showModalPassword" x-cloak><x-icon name="eye_off" class="w-4 h-4" /></span>
                            </button>
                        </div>
                        <p class="text-[10px] text-stone-500 font-sans mt-1">
                            * Jamaah menggunakan password sementara acak yang dikirimkan ke Email setelah verifikasi DP.
                        </p>
                    </div>

                    <!-- Tombol Masuk -->
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#0B3B24] hover:bg-emerald-900 text-white font-serif font-bold text-xs py-3.5 rounded-xl shadow-md transition cursor-pointer uppercase tracking-wider">
                            Masuk Sekarang
                        </button>
                    </div>
                </form>

                <div class="text-center pt-2 border-t border-stone-100">
                    <button @click="loginOpen = false" type="button" class="text-xs text-stone-500 hover:text-[#0B3B24] transition cursor-pointer font-serif">
                        ✕ Tutup Jendela Login
                    </button>
                </div>

            </div>

        </div>
    </div>
    @endguest
    <!-- penutup modal -->

</body>
</html>

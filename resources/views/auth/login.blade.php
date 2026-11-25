<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="darkreader-lock">
    <title>Masuk ke Akun - ROZWIA Islamic Travel</title>
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;600;700;800&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body { color-scheme: light only; }
        .font-serif-display { font-family: 'Abhaya Libre', 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen bg-[#EAE0D0] font-sans text-stone-800 antialiased flex items-center justify-center p-4 sm:p-6">

    <div class="w-full max-w-4xl bg-white rounded-[32px] shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 border border-stone-200/90">

        <!-- banner kiri -->
        <div class="hidden md:flex md:col-span-5 relative bg-[#0B3B24] p-8 flex-col justify-between overflow-hidden">
            <div class="absolute inset-0 opacity-25 bg-cover bg-center" style="background-image: url('{{ asset('images/makkah home.jpg') }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0B3B24] via-[#0B3B24]/85 to-[#0B3B24]/60"></div>

            <div class="relative z-10 flex items-center gap-3">
                <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA Logo" class="h-10 w-auto object-contain">
                <div>
                    <span class="block tracking-widest text-base font-bold text-white font-serif">ROZWIA</span>
                    <span class="block text-[9px] tracking-widest text-[#D4B996] uppercase">~ Islamic Travel ~</span>
                </div>
            </div>

            <div class="relative z-10 space-y-4 py-8 font-serif">
                <h2 class="text-3xl font-bold text-white leading-snug tracking-tight font-serif-display">
                    Dari Niat <br>
                    menjadi <br>
                    <span class="text-amber-300">Perjalanan Nyata</span>
                </h2>
                <p class="text-emerald-100/85 text-xs leading-relaxed font-sans">
                    Masuk ke Portal ROZWIA untuk memantau pendaftaran, kelengkapan dokumen, pembayaran, dan jadwal keberangkatan ibadah Bapak/Ibu.
                </p>
            </div>

            <div class="relative z-10 text-[10px] text-emerald-200/70 font-sans">
                © {{ date('Y') }} ROZWIA Islamic Travel
            </div>
        </div>

        <!-- form login -->
        <div class="md:col-span-7 p-6 sm:p-10 flex flex-col justify-center bg-white space-y-5"
             x-data="{ showPassword: false }">

            <!-- Logo Mobile -->
            <div class="flex md:hidden items-center justify-center gap-2.5 pb-1">
                <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA Logo" class="h-9 w-auto object-contain bg-[#0B3B24] rounded-xl p-1">
                <span class="font-serif-display text-xl font-bold text-[#0B3B24] tracking-wider">ROZWIA</span>
            </div>

            <div class="text-center md:text-left space-y-1">
                <span class="text-[10px] font-bold tracking-widest text-amber-800 uppercase">Selamat Datang Kembali · Akses Sistem</span>
                <h1 class="text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif-display">Masuk ke Akun Anda</h1>
                <p class="text-xs text-stone-500">Silakan masukkan email dan password yang telah dikirimkan oleh Admin.</p>
            </div>

            @if (session('success'))
                <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs leading-relaxed">
                    <strong class="block mb-0.5">✅ Pendaftaran Berhasil</strong>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('status'))
                <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="p-3.5 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl text-xs leading-relaxed">
                    {{ session('warning') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1">
                    <strong class="block">Gagal Masuk:</strong>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                        Email
                    </label>
                    <input type="text"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           placeholder="nama@gmail.com"
                           class="w-full px-4 py-3 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-[#0B3B24] text-xs bg-stone-50/50">
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-[#0B3B24] hover:underline">Lupa Password?</a>
                        @endif
                    </div>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'"
                               name="password"
                               id="password"
                               required
                               placeholder="••••••••"
                               class="w-full px-4 py-3 pr-11 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-[#0B3B24] text-xs bg-stone-50/50">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-3 flex items-center text-stone-500 hover:text-[#0B3B24] cursor-pointer"
                                title="Lihat / Sembunyikan Password">
                            <span x-show="!showPassword"><x-icon name="eye" class="w-4 h-4" /></span>
                            <span x-show="showPassword" x-cloak><x-icon name="eye_off" class="w-4 h-4" /></span>
                        </button>
                    </div>
                    <p class="text-[10px] text-stone-500">
                        * Jamaah menggunakan password sementara acak yang dikirimkan ke Email setelah verifikasi DP.
                    </p>
                </div>

                <div class="flex items-center justify-between pt-0.5">
                    <label class="inline-flex items-center gap-2 text-xs text-stone-600 cursor-pointer">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="rounded border-stone-300 text-[#0B3B24] focus:ring-[#0B3B24]">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <div class="pt-1">
                    <button type="submit" class="w-full bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold text-xs py-3.5 rounded-xl shadow-md transition cursor-pointer uppercase tracking-wider font-serif">
                        Masuk Sekarang
                    </button>
                </div>
            </form>

            <div class="flex items-center justify-between pt-3 border-t border-stone-100 text-xs text-stone-500">
                <a href="{{ route('landing') }}" class="hover:text-[#0B3B24] font-semibold transition inline-flex items-center gap-1">
                    <span>&larr;</span> <span>Kembali ke Beranda</span>
                </a>
                <a href="{{ route('paket.umroh') }}" class="text-[#0B3B24] font-bold hover:underline">Lihat Paket Ibadah</a>
            </div>

        </div>

    </div>

</body>
</html>

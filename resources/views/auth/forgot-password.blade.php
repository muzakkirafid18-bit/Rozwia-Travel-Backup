<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="darkreader-lock">
    <title>Lupa Password - ROZWIA Islamic Travel</title>
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;600;700;800&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body { color-scheme: light only; }
        .font-serif-display { font-family: 'Abhaya Libre', 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen bg-[#EAE0D0] font-sans text-stone-800 antialiased flex items-center justify-center p-4 sm:p-6">

    <div class="w-full max-w-4xl bg-white rounded-[32px] shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 border border-stone-200/90">

        <!-- SISI KIRI -->
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
                    Pulihkan Akses <br>
                    <span class="text-amber-300">Akun Jamaah</span>
                </h2>
                <p class="text-emerald-100/85 text-xs leading-relaxed font-sans">
                    Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan resmi untuk mengatur ulang password akun Bapak/Ibu.
                </p>
            </div>

            <div class="relative z-10 text-[10px] text-emerald-200/70 font-sans">
                © {{ date('Y') }} ROZWIA Islamic Travel
            </div>
        </div>

        <!-- SISI KANAN -->
        <div class="md:col-span-7 p-6 sm:p-10 flex flex-col justify-center bg-white space-y-5">
            <div class="text-center md:text-left space-y-1">
                <span class="text-[10px] font-bold tracking-widest text-amber-800 uppercase">Pemulihan Akun</span>
                <h1 class="text-2xl sm:text-3xl font-bold text-[#0B3B24] font-serif-display">Lupa Password?</h1>
                <p class="text-xs text-stone-500">Kami akan mengirimkan link reset password ke email Anda.</p>
            </div>

            @if (session('status'))
                <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs leading-relaxed">
                    ✅ {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                        Alamat Email Terdaftar
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           placeholder="nama@gmail.com"
                           class="w-full px-4 py-3 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-[#0B3B24] text-xs bg-stone-50/50">
                </div>

                <button type="submit" class="w-full bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold text-xs py-3.5 rounded-xl shadow-md transition cursor-pointer uppercase tracking-wider">
                    Kirim Link Reset Password
                </button>
            </form>

            <div class="pt-3 border-t border-stone-100 text-xs text-stone-500">
                <a href="{{ route('login') }}" class="hover:text-[#0B3B24] font-semibold transition inline-flex items-center gap-1">
                    <span>&larr;</span> <span>Kembali ke Halaman Login</span>
                </a>
            </div>
        </div>

    </div>

</body>
</html>

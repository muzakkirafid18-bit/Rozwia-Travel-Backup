<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="darkreader-lock">
    <title>Atur Ulang Password - ROZWIA Islamic Travel</title>
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

    <div class="w-full max-w-md bg-white rounded-[32px] shadow-2xl p-6 sm:p-8 border border-stone-200/90 space-y-5" x-data="{ show: false }">
        <div class="text-center space-y-2">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-[#0B3B24] grid place-items-center p-2.5 shadow-md">
                <img src="{{ asset('images/lOGO rozwia.png') }}" alt="ROZWIA" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-bold text-[#0B3B24] font-serif-display">Buat Password Baru</h1>
            <p class="text-xs text-stone-500">Silakan masukkan password baru untuk akun Anda.</p>
        </div>

        @if ($errors->any())
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1">
                @foreach ($errors->all() as $error)<p>⚠️ {{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="space-y-1">
                <label class="font-bold text-stone-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:outline-none focus:border-[#0B3B24]">
            </div>

            <div class="space-y-1">
                <label class="font-bold text-stone-700">Password Baru</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter (kombinasi huruf, angka, simbol)" class="w-full px-4 py-2.5 pr-16 rounded-xl border border-stone-300 focus:outline-none focus:border-[#0B3B24]">
                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-[#0B3B24] cursor-pointer" x-text="show ? 'Sembunyi' : 'Lihat'"></button>
                </div>
            </div>

            <div class="space-y-1">
                <label class="font-bold text-stone-700">Konfirmasi Password Baru</label>
                <input :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password baru" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-[#0B3B24]">
            </div>

            <button type="submit" class="w-full bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold py-3 rounded-xl shadow-md transition cursor-pointer uppercase tracking-wider">
                Simpan Password Baru
            </button>
        </form>
    </div>

</body>
</html>

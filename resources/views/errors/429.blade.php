<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terlalu Banyak Permintaan - Rozwia Islamic Travel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Playfair Display', serif; }
        .font-sans { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#F4EFE6] text-stone-800 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-[32px] p-8 shadow-xl border border-stone-200 text-center space-y-6">
        <div class="w-20 h-20 mx-auto rounded-full bg-[#0B3B24]/10 border border-[#0B3B24]/20 flex items-center justify-center text-[#0B3B24]">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold font-sans bg-amber-100 text-amber-800 border border-amber-200">
                Peringatan Keamanan (HTTP 429)
            </span>
            <h1 class="text-2xl font-bold text-[#0B3B24]">Terlalu Banyak Percobaan</h1>
            <p class="text-xs sm:text-sm text-stone-600 font-sans leading-relaxed">
                Demi keamanan sistem, akses sementara dibatasi karena terdapat terlalu banyak permintaan dalam waktu singkat. Mohon tunggu sekitar 1 menit sebelum mencoba kembali.
            </p>
        </div>
        <div class="pt-2 flex flex-col gap-2 font-sans text-xs">
            <a href="{{ url('/') }}" class="w-full bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold py-3 rounded-xl shadow-md transition">
                Kembali ke Beranda
            </a>
            <a href="javascript:history.back()" class="w-full bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 rounded-xl transition">
                &larr; Kembali ke Halaman Sebelumnya
            </a>
        </div>
    </div>
</body>
</html>

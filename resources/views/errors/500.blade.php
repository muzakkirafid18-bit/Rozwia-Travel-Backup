<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Gangguan Server - Rozwia Islamic Travel</title>
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
        <div class="w-20 h-20 mx-auto rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-700">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold font-sans bg-rose-100 text-rose-800 border border-rose-200">
                Error 500 - Kesalahan Server
            </span>
            <h1 class="text-2xl font-bold text-[#0B3B24]">Terjadi Gangguan Teknis</h1>
            <p class="text-xs sm:text-sm text-stone-600 font-sans leading-relaxed">
                Mohon maaf, server kami sedang mengalami kendala sesaat saat memproses permintaan Anda. Tim kami telah mencatat kendala ini dan sedang memperbaikinya.
            </p>
        </div>
        <div class="pt-2 flex flex-col gap-2 font-sans text-xs">
            <a href="{{ url('/') }}" class="w-full bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold py-3 rounded-xl shadow-md transition">
                Kembali ke Beranda
            </a>
            <a href="javascript:history.back()" class="w-full bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 rounded-xl transition">
                &larr; Coba Lagi
            </a>
        </div>
    </div>
</body>
</html>

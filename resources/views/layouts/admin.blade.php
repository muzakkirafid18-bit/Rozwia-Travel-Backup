<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="darkreader-lock">
    <title>@yield('title', 'Admin Panel - ROZWIA Islamic Travel')</title>
    @include('partials.favicon')

    <!-- styling vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- alpinejs -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            color-scheme: light only;
        }
        .font-serif-display {
            font-family: 'Abhaya Libre', 'Playfair Display', serif;
        }
    </style>
</head>

<body class="bg-[#EAE0D0] font-sans text-stone-800 antialiased min-h-screen lg:flex overflow-x-hidden"
      x-data="{ sidebarOpen: false }">

    <!-- header mobile -->
    <header class="lg:hidden sticky top-0 z-30 bg-[#0B3B24] text-white px-4 py-3 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-2.5 font-serif">
            <img src="{{ asset('images/lOGO rozwia.png') }}" alt="Logo ROZWIA" class="h-8 w-8 object-contain">
            <div class="leading-tight">
                <span class="block tracking-widest text-sm font-bold text-white">ROZWIA</span>
                <span class="block text-[9px] tracking-widest text-[#E5D3B3]">ADMIN PANEL</span>
            </div>
        </div>
        <button type="button" @click="sidebarOpen = true" class="inline-flex items-center justify-center rounded-xl border border-emerald-700 bg-emerald-900/60 p-2 text-[#E5D3B3] cursor-pointer" aria-label="Buka menu admin">
            <x-icon name="menu" class="w-5 h-5" />
        </button>
    </header>

    <!-- overlay backdrop mobile -->
    <div x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         style="display: none;"
         class="fixed inset-0 z-30 bg-black/50 backdrop-blur-xs lg:hidden"></div>

    <!-- sidebar menu -->
    <aside class="w-72 bg-[#0B3B24] text-[#E5D3B3] flex flex-col justify-between fixed inset-y-0 left-0 z-40 shadow-xl p-6 rounded-r-[32px] overflow-y-auto transition-transform duration-300 lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <div class="space-y-8">
            <!-- Logo Header Sidebar -->
            <div class="flex items-center justify-between gap-3 pb-4 border-b border-emerald-800/80 font-serif">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/lOGO rozwia.png') }}" alt="Logo ROZWIA" class="h-11 w-11 object-contain shrink-0">
                    <div class="leading-tight">
                        <span class="block tracking-widest text-lg font-bold text-white">ROZWIA</span>
                        <span class="block text-[9px] tracking-widest text-[#E5D3B3]">~ ISLAMIC TRAVEL ~</span>
                    </div>
                </div>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-[#E5D3B3] hover:text-white p-1 cursor-pointer" aria-label="Tutup menu">
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>

            <!-- Menu Navigasi Sidebar -->
            <nav class="space-y-3 font-serif text-xs">
                <!-- 1. Beranda -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('admin.dashboard') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="home" class="w-5 h-5" />
                    <span>Beranda</span>
                </a>

                <!-- 2. Aktivasi Akun -->
                <a href="{{ route('admin.aktivasi') }}" 
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('admin.aktivasi*') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="account_box" class="w-5 h-5" />
                    <span>Aktivasi Akun</span>
                </a>

                <!-- 3. Verifikasi Dokumen Pribadi -->
                <a href="{{ route('admin.dokumen') }}" 
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('admin.dokumen') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="scan" class="w-5 h-5" />
                    <span>Verifikasi Dokumen Pribadi</span>
                </a>

                <!-- 4. Kelola Pembayaran -->
                <a href="{{ route('admin.pembayaran') }}" 
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('admin.pembayaran') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="payments" class="w-5 h-5" />
                    <span>Kelola Pembayaran</span>
                </a>

                <!-- 5. Status Surat Kemenag & Visa -->
                <a href="{{ route('admin.kemenag') }}" 
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('admin.kemenag') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="passport" class="w-5 h-5" />
                    <span>Status Surat Kemenag &amp; Visa</span>
                </a>

                <!-- 6. Jadwal & Manifest -->
                <a href="{{ route('admin.manifest') }}" 
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('admin.manifest') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="edit_calendar" class="w-5 h-5" />
                    <span>Jadwal &amp; Manifest</span>
                </a>
            </nav>
        </div>

        <!-- profil & tombol keluar -->
        <div class="pt-6 border-t border-emerald-800/80 flex flex-col gap-3 font-serif">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-10 h-10 rounded-full bg-emerald-800 border border-[#E5D3B3]/40 flex items-center justify-center text-amber-200 shrink-0">
                    <x-icon name="person" class="w-5 h-5" />
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <span class="text-[10px] text-amber-300 font-sans">Admin</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-[#DC2626] hover:bg-red-700 text-white font-bold text-xs py-2.5 px-4 rounded-full transition shadow-md cursor-pointer flex items-center justify-center gap-1.5">
                    <x-icon name="logout" class="w-4 h-4" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- konten utama -->
    <main class="flex-1 min-w-0 w-full lg:ml-72 min-h-screen p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- toast notifikasi -->
            @if(session('success'))
            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 6000)"
                 x-show="show"
                 x-transition
                 class="fixed top-6 right-6 z-[100] max-w-md w-[calc(100%-3rem)] bg-emerald-900 border-2 border-emerald-500 text-white p-4 rounded-2xl shadow-2xl flex items-start justify-between gap-3 font-serif">
                <div class="flex items-start gap-3">
                    <x-icon name="check_circle" class="w-5 h-5 text-emerald-300 mt-0.5" />
                    <div>
                        <strong class="font-bold text-sm text-emerald-200 block">Berhasil!</strong>
                        <p class="text-xs text-white leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-emerald-200 hover:text-white text-lg font-bold px-1.5 cursor-pointer transition shrink-0">&times;</button>
            </div>
            @endif

            @if(session('info'))
            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 6000)"
                 x-show="show"
                 x-transition
                 class="fixed top-6 right-6 z-[100] max-w-md w-[calc(100%-3rem)] bg-sky-800 border-2 border-sky-400 text-white p-4 rounded-2xl shadow-2xl flex items-start justify-between gap-3 font-serif">
                <div class="flex items-start gap-3">
                    <x-icon name="info" class="w-5 h-5 text-sky-300 mt-0.5" />
                    <div>
                        <strong class="font-bold text-sm text-sky-200 block">Info</strong>
                        <p class="text-xs text-white leading-relaxed">{{ session('info') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-sky-200 hover:text-white text-lg font-bold px-1.5 cursor-pointer transition shrink-0">&times;</button>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 8000)"
                 x-show="show"
                 x-transition
                 class="fixed top-6 right-6 z-[100] max-w-md w-[calc(100%-3rem)] bg-rose-900 border-2 border-rose-500 text-white p-4 rounded-2xl shadow-2xl flex items-start justify-between gap-3 font-serif">
                <div class="flex items-start gap-3">
                    <x-icon name="warning" class="w-5 h-5 text-rose-300 mt-0.5" />
                    <div>
                        <strong class="font-bold text-sm text-rose-200 block">Perhatian!</strong>
                        <p class="text-xs text-white leading-relaxed">{{ session('error') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-rose-200 hover:text-white text-lg font-bold px-1.5 cursor-pointer transition shrink-0">&times;</button>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        (function () {
            const key = 'rozwia_scroll_' + window.location.pathname;
            const saved = sessionStorage.getItem(key);
            if (saved !== null) {
                sessionStorage.removeItem(key);
                window.addEventListener('DOMContentLoaded', () => {
                    window.scrollTo({ top: parseInt(saved, 10) || 0, behavior: 'instant' });
                });
            }
            const remember = () => sessionStorage.setItem(key, String(window.scrollY));
            document.addEventListener('submit', remember, true);
            document.addEventListener('click', (e) => {
                const a = e.target.closest('a[href]');
                if (!a) return;
                try {
                    const url = new URL(a.href, window.location.origin);
                    if (url.origin === window.location.origin && url.pathname === window.location.pathname && !url.hash) {
                        remember();
                    }
                } catch (_) {}
            }, true);
        })();
    </script>

</body>
</html>

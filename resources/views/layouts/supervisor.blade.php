<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="darkreader-lock">
    <title>@yield('title', 'Supervisor Panel - ROZWIA Islamic Travel')</title>
    @include('partials.favicon')

    <!-- styling vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-[#F4EFE6] font-sans text-stone-800 antialiased min-h-screen lg:flex overflow-x-hidden"
      x-data="{ sidebarOpen: false }">

    <!-- header mobile -->
    <header class="lg:hidden sticky top-0 z-30 bg-[#0B3B24] text-white px-4 py-3 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-2.5 font-serif">
            <img src="{{ asset('images/lOGO rozwia.png') }}" alt="Logo ROZWIA" class="h-8 w-8 object-contain">
            <div class="leading-tight">
                <span class="block tracking-widest text-sm font-bold text-white">ROZWIA</span>
                <span class="block text-[9px] tracking-widest text-[#E5D3B3]">SUPERVISOR PANEL</span>
            </div>
        </div>
        <button type="button" @click="sidebarOpen = true" class="inline-flex items-center justify-center rounded-xl border border-emerald-700 bg-emerald-900/60 p-2 text-[#E5D3B3] cursor-pointer" aria-label="Buka menu supervisor">
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
    <aside class="w-72 bg-[#0B3B24] text-[#E5D3B3] flex flex-col justify-between fixed inset-y-0 left-0 z-40 shadow-xl p-6 overflow-y-auto transition-transform duration-300 lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="space-y-8">
            <!-- logo sidebar -->
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

            <!-- menu sidebar -->
            <nav class="space-y-2 font-serif text-sm">
                <a href="{{ route('supervisor.dashboard') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('supervisor.dashboard') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="home_work" class="w-5 h-5" />
                    <span>Beranda</span>
                </a>

                <a href="{{ route('supervisor.profile') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('supervisor.profile', 'supervisor.perusahaan', 'supervisor.rekening*') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="home_work" class="w-5 h-5" />
                    <span>Profil Perusahaan</span>
                </a>

                <a href="{{ route('supervisor.paket') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('supervisor.paket*', 'supervisor.maskapai*', 'supervisor.hotel*') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="box_edit" class="w-5 h-5" />
                    <span>Kelola Paket</span>
                </a>

                <a href="{{ route('supervisor.users') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('supervisor.users*', 'supervisor.akun') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="person" class="w-5 h-5" />
                    <span>Daftar Akun Pengguna</span>
                </a>

                <a href="{{ route('supervisor.biaya') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('supervisor.biaya*') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="money_bag" class="w-5 h-5" />
                    <span>Total Biaya</span>
                </a>
            </nav>
        </div>

        <!-- profil & tombol keluar -->
        <div class="pt-6 border-t border-emerald-800/80 flex flex-col space-y-4 font-serif">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-10 h-10 rounded-full bg-emerald-800 border border-[#E5D3B3]/40 flex items-center justify-center text-amber-200 shrink-0">
                    <x-icon name="person" class="w-5 h-5" />
                </div>
                <div class="overflow-hidden leading-tight">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name ?? 'Supervisor' }}</p>
                    <span class="text-[10px] text-emerald-300">Akses Supervisor</span>
                </div>
            </div>

            <form action="{{ route('supervisor.logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shadow-md">
                    <x-icon name="logout" class="w-4 h-4" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- konten utama -->
    <main class="flex-1 min-w-0 w-full lg:ml-72 min-h-screen p-4 sm:p-6 lg:p-8 font-serif">
        <div class="max-w-6xl mx-auto space-y-6">

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-100 text-emerald-900 border border-emerald-200 text-xs font-sans font-bold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <x-icon name="check_circle" class="w-5 h-5 text-emerald-700" />
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 font-bold cursor-pointer">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-100 text-rose-900 border border-rose-200 text-xs font-sans font-bold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <x-icon name="warning" class="w-5 h-5 text-rose-700" />
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 font-bold cursor-pointer">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-100 text-rose-900 border border-rose-200 text-xs font-sans space-y-1 shadow-xs">
                    <div class="flex items-center gap-2 font-bold">
                        <x-icon name="error" class="w-5 h-5 text-rose-700" />
                        <span>Terdapat isian yang perlu diperbaiki:</span>
                    </div>
                    <ul class="list-disc list-inside pl-6 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
            @yield('supervisor_content')

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

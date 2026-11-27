<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="darkreader-lock">
    <title>@yield('title', 'Super Admin Panel - ROZWIA Islamic Travel')</title>
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body { color-scheme: light only; }
        .font-serif-display { font-family: 'Abhaya Libre', 'Playfair Display', serif; }
        [x-cloak] { display: none !important; }
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
                <span class="block text-[9px] tracking-widest text-[#E5D3B3]">SUPER ADMIN</span>
            </div>
        </div>
        <button type="button" @click="sidebarOpen = true" class="inline-flex items-center justify-center rounded-xl border border-emerald-700 bg-emerald-900/60 p-2 text-[#E5D3B3] cursor-pointer" aria-label="Buka menu Super Admin">
            <x-icon name="menu" class="w-5 h-5" />
        </button>
    </header>

    <!-- overlay backdrop mobile -->
    <div x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         style="display: none;"
         class="fixed inset-0 z-30 bg-black/50 backdrop-blur-xs lg:hidden"></div>

    <!-- sidebar super admin -->
    <aside class="w-72 bg-[#0B3B24] text-[#E5D3B3] flex flex-col justify-between fixed inset-y-0 left-0 z-40 shadow-xl p-6 rounded-r-[32px] overflow-y-auto transition-transform duration-300 lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="space-y-8">
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

            <nav class="space-y-2.5 font-serif text-xs">
                <a href="{{ route('administrator.dashboard') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('administrator.dashboard', 'administrator.users*') && !request('role') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="manage_accounts" class="w-5 h-5" />
                    <span>Manajemen Akun</span>
                </a>

                <a href="{{ route('administrator.role-matrix') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request()->routeIs('administrator.role-matrix*') ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="checklist" class="w-5 h-5" />
                    <span>Ceklis Role Akun</span>
                </a>

                <a href="{{ route('administrator.dashboard', ['role' => 'administrator']) }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request('role') === 'administrator' ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="shield_person" class="w-5 h-5" />
                    <span>Akun Super Admin</span>
                </a>

                <a href="{{ route('administrator.dashboard', ['role' => 'supervisor']) }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request('role') === 'supervisor' ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="supervisor_account" class="w-5 h-5" />
                    <span>Akun Supervisor</span>
                </a>

                <a href="{{ route('administrator.dashboard', ['role' => 'admin']) }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request('role') === 'admin' ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="admin_panel_settings" class="w-5 h-5" />
                    <span>Akun Admin</span>
                </a>

                <a href="{{ route('administrator.dashboard', ['role' => 'jamaah']) }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left {{ request('role') === 'jamaah' ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="groups" class="w-5 h-5" />
                    <span>Akun Jamaah</span>
                </a>

                <div class="pt-3 mt-3 border-t border-emerald-800/70">
                    <a href="{{ route('landing') }}"
                       class="w-full flex items-center gap-3 px-4 py-2.5 rounded-2xl text-[#E5D3B3]/80 hover:bg-emerald-900/50 hover:text-white transition text-left">
                        <x-icon name="public" class="w-5 h-5" />
                        <span>Lihat Beranda Publik</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- profil & tombol keluar -->
        <div class="space-y-3 pt-6 border-t border-emerald-800/70 font-serif">
            <div class="flex items-center gap-3 px-2">
                <div class="w-10 h-10 rounded-full bg-emerald-800 border border-[#E5D3B3]/40 flex items-center justify-center text-amber-200 shrink-0">
                    <x-icon name="person" class="w-5 h-5" />
                </div>
                <div class="min-w-0 leading-tight">
                    <strong class="block text-xs font-bold text-white truncate">{{ auth()->user()->name }}</strong>
                    <span class="text-[10px] text-amber-300/90 font-sans">Super Admin</span>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shadow-md font-sans">
                    <x-icon name="logout" class="w-4 h-4" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- konten utama -->
    <main class="flex-1 min-w-0 w-full lg:ml-72 min-h-screen p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto space-y-6">

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
                        <p class="text-xs text-white leading-relaxed font-sans">{{ session('success') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-emerald-200 hover:text-white text-lg font-bold px-1.5 cursor-pointer transition shrink-0">&times;</button>
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
                        <p class="text-xs text-white leading-relaxed font-sans">{{ session('error') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-rose-200 hover:text-white text-lg font-bold px-1.5 cursor-pointer transition shrink-0">&times;</button>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>

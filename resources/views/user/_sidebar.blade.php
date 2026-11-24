@php
    $active = $active ?? 'beranda';
    $userName = auth()->user()->name ?? 'Jamaah';
@endphp

<div x-data="{ sidebarOpen: false }" class="contents">
    {{-- Mobile Top Bar --}}
    <div class="lg:hidden sticky top-0 z-30 flex items-center justify-between bg-[#0B3B24] px-5 py-3.5 text-[#E5D3B3] shadow-md">
        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/lOGO rozwia.png') }}" alt="Logo Rozwia" class="h-9 w-9 object-contain">
            <div class="leading-tight">
                <span class="block font-serif text-base font-bold tracking-widest text-white">ROZWIA</span>
                <span class="block text-[9px] tracking-[0.2em] text-[#E5D3B3]">~ ISLAMIC TRAVEL ~</span>
            </div>
        </a>
        <button type="button" @click="sidebarOpen = !sidebarOpen" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-900/70 text-[#E5D3B3] hover:bg-emerald-800 transition cursor-pointer" aria-label="Buka menu">
            <span x-show="!sidebarOpen"><x-icon name="menu" class="w-5 h-5" /></span>
            <span x-show="sidebarOpen" x-cloak><x-icon name="close" class="w-5 h-5" /></span>
        </button>
    </div>

    {{-- Mobile Overlay --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden" style="display: none;"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 w-72 lg:w-full lg:sticky lg:top-0 lg:h-screen bg-[#0B3B24] text-[#E5D3B3] flex flex-col justify-between p-7 lg:p-8 overflow-y-auto shadow-lg transition-transform duration-200 ease-out">
        <div class="space-y-9">
            {{-- Logo Rozwia --}}
            <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 pb-5 border-b border-emerald-800/80">
                <img src="{{ asset('images/lOGO rozwia.png') }}" alt="Logo Rozwia" class="w-12 h-12 object-contain shrink-0">
                <div class="leading-tight">
                    <span class="block font-serif tracking-widest text-lg font-bold text-white">ROZWIA</span>
                    <span class="block text-[9px] tracking-[0.2em] text-[#E5D3B3]">~ ISLAMIC TRAVEL ~</span>
                </div>
            </a>

            {{-- Menu Navigasi --}}
            <nav class="space-y-2.5 font-serif">
                <a href="{{ route('user.dashboard') }}"
                   class="flex items-center gap-4 px-5 py-3.5 rounded-2xl transition {{ $active === 'beranda' ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="home" class="w-5 h-5" />
                    <span class="text-base font-semibold">Beranda</span>
                </a>

                <a href="{{ route('user.profile') }}"
                   class="flex items-center gap-4 px-5 py-3.5 rounded-2xl transition {{ $active === 'profil' ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="account_circle" class="w-5 h-5" />
                    <span class="text-base font-semibold">Profil</span>
                </a>

                <a href="{{ route('user.dokumen') }}"
                   class="flex items-center gap-4 px-5 py-3.5 rounded-2xl transition {{ $active === 'dokumen' ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="description" class="w-5 h-5" />
                    <span class="text-base font-semibold">Dokumen</span>
                </a>

                <a href="{{ route('user.tracking') }}"
                   class="flex items-center gap-4 px-5 py-3.5 rounded-2xl transition {{ $active === 'perjalanan' ? 'bg-[#E5D3B3] text-black font-bold shadow-md' : 'text-[#E5D3B3] hover:bg-emerald-900/60' }}">
                    <x-icon name="pin_drop" class="w-5 h-5" />
                    <span class="text-base font-semibold">Status Perjalanan</span>
                </a>
            </nav>
        </div>

        {{-- Bagian Bawah Sidebar (User Info & Keluar) --}}
        <div class="space-y-5 pt-6 mt-8 border-t border-emerald-800/80">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-800 border border-[#E5D3B3]/40 flex items-center justify-center text-amber-200 shrink-0">
                    <x-icon name="person" class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <strong class="block truncate text-white text-base font-serif">{{ $userName }}</strong>
                    <span class="text-xs text-[#E5D3B3]">Jamaah ROZWIA</span>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-3 px-4 rounded-2xl flex items-center justify-center gap-2 transition shadow-md cursor-pointer font-serif">
                    <x-icon name="logout" class="w-5 h-5" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>
</div>

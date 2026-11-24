@extends('layouts.supervisor')

@section('title', 'Daftar Akun Pengguna - Supervisor ROZWIA')

@section('content')
<div class="space-y-6">

    <!-- Header Card: Daftar Akun Pengguna -->
    <div class="bg-white/80 backdrop-blur-md border border-stone-200/80 rounded-[32px] p-6 sm:p-7 shadow-xs space-y-1">
        <h1 class="text-2xl sm:text-3xl font-[800] font-['Abhaya_Libre'] text-stone-900">
            Daftar Akun Pengguna
        </h1>
        <p class="text-xs sm:text-sm text-stone-500 font-sans">
            Tracking dan pantau akun pengguna
        </p>
    </div>

    <!-- 3 Kartu Statistik Ringkasan -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 font-serif">
        <div class="bg-white p-6 sm:p-7 rounded-[32px] border border-stone-200 shadow-sm space-y-1">
            <span class="text-xs font-[700] uppercase tracking-wider text-stone-400 font-sans">Total Akun</span>
            <h3 class="text-3xl sm:text-4xl font-[800] text-stone-900 font-['Abhaya_Libre']">{{ $totalAkun }}</h3>
        </div>
        <div class="bg-white p-6 sm:p-7 rounded-[32px] border border-stone-200 shadow-sm space-y-1">
            <span class="text-xs font-[700] uppercase tracking-wider text-stone-400 font-sans">Akun Aktif</span>
            <h3 class="text-3xl sm:text-4xl font-[800] text-emerald-800 font-['Abhaya_Libre']">{{ $akunAktif }}</h3>
        </div>
        <div class="bg-white p-6 sm:p-7 rounded-[32px] border border-stone-200 shadow-sm space-y-1">
            <span class="text-xs font-[700] uppercase tracking-wider text-stone-400 font-sans">Akun Nonaktif</span>
            <h3 class="text-3xl sm:text-4xl font-[800] text-rose-700 font-['Abhaya_Libre']">{{ $akunNonaktif }}</h3>
        </div>
    </div>

    <!-- Tabel & Filter Section -->
    <div class="bg-white rounded-[32px] p-6 sm:p-8 shadow-sm border border-stone-200 space-y-6 font-sans">

        <!-- Bagian Atas Tabel: Judul, Search, Download, & Filter Status -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h3 class="text-base font-[800] text-stone-900 font-['Abhaya_Libre']">Daftar Akun Jamaah</h3>
                <p class="text-[11px] text-stone-400">Menampilkan {{ $users->count() }} dari {{ $users->total() }} akun</p>
            </div>

            <form action="{{ route('supervisor.users') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <!-- Search Input -->
                <div class="relative flex-1 sm:flex-initial">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400 text-sm"><x-icon name="search" class="w-4 h-4" /></span>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari Nama Akun Jamaah"
                           class="w-full sm:w-64 pl-10 pr-4 py-2.5 rounded-full bg-white border border-stone-200 text-xs font-medium focus:outline-none focus:border-stone-400 shadow-xs">
                </div>

                <!-- Tombol Download Data Akun -->
                <a href="{{ route('supervisor.users.export', ['search' => $search, 'status' => $status]) }}"
                   class="bg-white hover:bg-stone-50 text-stone-800 border border-stone-200 font-[700] text-xs py-2.5 px-5 rounded-full transition cursor-pointer flex items-center gap-2 shadow-xs">
                    <x-icon name="download_2" class="w-4 h-4" />
                    <span>Download Data Akun</span>
                </a>

                <!-- Filter "Semua Status" -->
                <div class="relative">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="appearance-none bg-white border border-stone-200 text-stone-800 font-[700] text-xs py-2.5 pl-4 pr-10 rounded-full focus:outline-none focus:border-stone-400 cursor-pointer shadow-xs">
                        <option value="semua" {{ $status === 'semua' || !$status ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-stone-500 text-xs"><x-icon name="arrow_drop_down" class="w-4 h-4" /></span>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Akun Pengguna -->
        <div class="overflow-x-auto rounded-2xl border border-stone-200">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-100/80 text-stone-600 font-serif border-b border-stone-200">
                        <th class="p-3.5 font-[700]">Nama Pengguna</th>
                        <th class="p-3.5 font-[700]">Email</th>
                        <th class="p-3.5 font-[700]">No Telepon</th>
                        <th class="p-3.5 font-[700]">Paket</th>
                        <th class="p-3.5 font-[700]">Tgl Registrasi</th>
                        <th class="p-3.5 font-[700] text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-stone-700">
                    @forelse($users as $u)
                    @php
                        $words = explode(' ', trim($u->name));
                        $initials = '';
                        foreach (array_slice($words, 0, 2) as $w) {
                            $initials .= strtoupper(mb_substr($w, 0, 1));
                        }
                        $initials = $initials ?: 'JM';
                        $paket = $u->bookings->first()?->jadwal?->paket?->nama_paket ?? '-';
                        $isAktif = $u->status_akun === 'active';
                    @endphp
                    <tr class="hover:bg-stone-50/80 transition">
                        <td class="p-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-emerald-100 text-[#0B3B24] font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $initials }}
                                </div>
                                <span class="font-bold text-stone-900 font-serif">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="p-3.5 text-stone-600">{{ $u->email }}</td>
                        <td class="p-3.5 text-stone-600">{{ $u->nomor_telepon ?: '-' }}</td>
                        <td class="p-3.5 font-medium text-stone-800">{{ $paket }}</td>
                        <td class="p-3.5 text-stone-500">{{ $u->created_at?->format('d/m/Y') ?? '-' }}</td>
                        <td class="p-3.5 text-center">
                            @if($isAktif)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200/60 text-[10px] font-bold px-3 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 border border-rose-200/60 text-[10px] font-bold px-3 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-stone-400">
                            Tidak ada akun jamaah yang sesuai filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="pt-2">
            {{ $users->links() }}
        </div>
        @endif

    </div>
</div>
@endsection

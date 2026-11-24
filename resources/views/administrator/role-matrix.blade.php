@extends('layouts.administrator')

@section('title', 'Ceklis Role Akun - Super Admin ROZWIA')

@section('content')
@php
    $roleColumns = [
        'administrator' => ['label' => 'Super Admin', 'badge' => 'text-purple-800 bg-purple-50 border-purple-200'],
        'supervisor'    => ['label' => 'Supervisor', 'badge' => 'text-amber-900 bg-amber-50 border-amber-200'],
        'admin'         => ['label' => 'Admin', 'badge' => 'text-sky-800 bg-sky-50 border-sky-200'],
        'jamaah'        => ['label' => 'Jamaah', 'badge' => 'text-emerald-800 bg-emerald-50 border-emerald-200'],
    ];

    $initialRoles = $users->mapWithKeys(fn ($u) => [$u->id => $u->role])->all();
@endphp

<div class="space-y-6"
     x-data="{
         selectedRoles: {{ Js::from($initialRoles) }},
         setRole(userId, role, isProtected) {
             if (isProtected) return;
             this.selectedRoles[userId] = role;
         }
     }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/85 backdrop-blur-md border border-stone-200/80 rounded-[28px] p-6 shadow-xs">
        <div class="space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-800">Penugasan Hak Akses Cepat</span>
            <h1 class="text-2xl sm:text-3xl font-bold font-serif-display text-stone-900">Matriks Ceklis Role Akun</h1>
            <p class="text-xs sm:text-sm text-stone-500">
                Centang role untuk setiap akun (<strong>Super Admin</strong>, <strong>Supervisor</strong>, <strong>Admin</strong>, atau <strong>Jamaah</strong>), lalu klik <strong>Simpan Perubahan Role</strong>.
            </p>
        </div>

        <a href="{{ route('administrator.dashboard') }}"
           class="inline-flex items-center justify-center gap-1.5 rounded-2xl border border-stone-300 bg-white hover:bg-stone-100 text-stone-700 px-4 py-2.5 text-xs font-bold transition shrink-0">
            <x-icon name="arrow_back" class="w-4 h-4" />
            <span>Kembali ke Daftar Akun</span>
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 space-y-1">
            <strong class="block font-bold">Terjadi kesalahan:</strong>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-[28px] border border-stone-200 bg-white shadow-sm overflow-hidden">
        <!-- Bar Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 px-6 py-4 bg-stone-50/50">
            <p class="text-xs text-stone-600">
                Setiap akun memiliki <strong>1 role aktif</strong>. Akun Anda sendiri dan Super Admin aktif terakhir dilindungi otomatis.
            </p>

            <form method="GET" action="{{ route('administrator.role-matrix') }}" class="flex items-center gap-2">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama / email..."
                       class="w-full sm:w-56 rounded-xl border border-stone-300 bg-white px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                <button type="submit" class="rounded-xl bg-[#0B3B24] hover:bg-emerald-900 px-4 py-2 text-xs font-bold text-white cursor-pointer transition">
                    Cari
                </button>
            </form>
        </div>

        <form method="POST" action="{{ route('administrator.role-matrix.update') }}">
            @csrf

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-100 text-stone-700">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Akun Pengguna</th>
                            <th class="px-3 py-3.5 text-center font-semibold">Status</th>
                            @foreach($roleColumns as $colKey => $colInfo)
                                <th class="px-4 py-3.5 text-center font-semibold whitespace-nowrap">{{ $colInfo['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @forelse($users as $u)
                        @php
                            $isSelf = $u->id === auth()->id();
                            $isLastAdmin = $u->role === 'administrator' && $u->status_akun === 'active' && $activeAdminCount <= 1;
                            $isLocked = $isSelf || $isLastAdmin;
                        @endphp
                        <tr class="hover:bg-stone-50/80 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-[#0B3B24]">{{ $u->name }}</span>
                                            @if($isSelf)
                                                <span class="rounded-full bg-[#0B3B24] px-2 py-0.5 text-[9px] font-bold text-amber-200">Anda</span>
                                            @elseif($isLastAdmin)
                                                <span class="rounded-full bg-purple-100 px-2 py-0.5 text-[9px] font-bold text-purple-800">Admin Utama</span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-stone-500 block">{{ $u->email }}</span>
                                    </div>
                                </div>
                                <input type="hidden" name="roles[{{ $u->id }}]" :value="selectedRoles[{{ $u->id }}]">
                            </td>

                            <td class="px-3 py-3.5 text-center">
                                @if($u->status_akun === 'active')
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-800">Active</span>
                                @else
                                    <span class="rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold text-rose-700">Inactive</span>
                                @endif
                            </td>

                            @foreach($roleColumns as $colKey => $colInfo)
                                <td class="px-4 py-3.5 text-center">
                                    <label class="inline-flex items-center justify-center p-1.5 rounded-lg border transition {{ $isLocked ? 'opacity-50 cursor-not-allowed border-stone-200 bg-stone-50' : 'cursor-pointer hover:bg-stone-100 border-transparent' }}"
                                           :class="selectedRoles[{{ $u->id }}] === '{{ $colKey }}' ? '{{ $colInfo['badge'] }}' : ''">
                                        <input type="checkbox"
                                               class="h-4 w-4 rounded accent-[#0B3B24] {{ $isLocked ? 'cursor-not-allowed' : 'cursor-pointer' }}"
                                               :checked="selectedRoles[{{ $u->id }}] === '{{ $colKey }}'"
                                               @disabled($isLocked)
                                               @change="setRole({{ $u->id }}, '{{ $colKey }}', {{ $isLocked ? 'true' : 'false' }})">
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-stone-500">Tidak ada akun pengguna yang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-stone-200 bg-stone-50/70 px-6 py-4">
                <div class="text-xs text-stone-500">
                    @if($users->hasPages())
                        {{ $users->links() }}
                    @else
                        Menampilkan {{ $users->count() }} akun pada halaman ini.
                    @endif
                </div>

                <button type="submit"
                        onclick="return confirm('Simpan seluruh perubahan role pada daftar ini?')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0B3B24] hover:bg-emerald-900 px-6 py-3 text-xs font-bold text-white shadow-md transition cursor-pointer">
                    <x-icon name="save" class="w-4 h-4" />
                    <span>Simpan Perubahan Role</span>
                </button>
            </div>
        </form>
    </section>

</div>
@endsection

@extends('layouts.supervisor')

@section('title', 'Hotel - Supervisor ROZWIA')

@section('content')
<div class="space-y-6 font-serif">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <a href="{{ route('supervisor.paket') }}" class="inline-flex items-center gap-1 text-xs font-sans font-bold text-[#063B25] hover:underline">
                <x-icon name="arrow_back" class="w-4 h-4" />
                <span>Kembali ke Kelola Paket</span>
            </a>
            <h1 class="font-serif-display text-3xl font-bold text-stone-900 tracking-tight">Hotel Makkah &amp; Madinah</h1>
            <p class="text-xs text-stone-600 font-sans">Data hotel yang tersedia untuk dipilih pada paket Haji dan Umroh.</p>
        </div>

        <a href="{{ route('supervisor.hotel.create') }}"
           class="bg-[#063B25] hover:bg-emerald-900 text-white font-sans font-bold text-xs py-3 px-5 rounded-2xl transition shadow-sm inline-flex items-center gap-1.5 shrink-0">
            <x-icon name="add" class="w-4 h-4" />
            <span>Tambah Hotel Baru</span>
        </a>
    </div>

    <div class="bg-white rounded-[28px] shadow-xs border border-stone-200/80 overflow-hidden font-sans max-w-5xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#063B25] text-white">
                        <th class="py-3.5 px-5 font-semibold w-16">No.</th>
                        <th class="py-3.5 px-5 font-semibold">Nama Hotel</th>
                        <th class="py-3.5 px-5 font-semibold">Lokasi Kota</th>
                        <th class="py-3.5 px-5 font-semibold">Bintang</th>
                        <th class="py-3.5 px-5 font-semibold">Alamat / Keterangan</th>
                        <th class="py-3.5 px-5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-stone-700">
                    @forelse($hotels as $index => $h)
                    @php
                        $thumb = $h->foto_hotel
                            ? asset($h->foto_hotel)
                            : asset($h->lokasi === 'Madinah' ? 'images/crowne.jpg' : 'images/sheraton.jpg');
                    @endphp
                    <tr class="hover:bg-stone-50/80 transition">
                        <td class="py-3.5 px-5 text-stone-500">{{ $hotels->firstItem() + $index }}</td>
                        <td class="py-3.5 px-5 font-bold text-[#063B25]">
                            <span class="inline-flex items-center gap-2.5">
                                <img src="{{ $thumb }}" alt="{{ $h->nama_hotel }}" class="h-9 w-12 rounded-lg object-cover border border-stone-200 shrink-0">
                                <span>{{ $h->nama_hotel }}</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold {{ $h->lokasi === 'Makkah' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $h->lokasi }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-amber-600 font-bold">
                            {{ str_repeat('★', (int) ($h->bintang_hotel ?? 5)) }}
                        </td>
                        <td class="py-3.5 px-5 text-stone-500">{{ $h->alamat_hotel ?: '-' }}</td>
                        <td class="py-3.5 px-5 text-right whitespace-nowrap space-x-3">
                            <a href="{{ route('supervisor.hotel.edit', $h) }}" class="font-bold text-[#063B25] hover:underline">Edit</a>
                            <form action="{{ route('supervisor.hotel.destroy', $h) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hotel ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-bold text-rose-600 hover:underline cursor-pointer">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-500 italic">Belum ada hotel yang didaftarkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($hotels->hasPages())
        <div class="p-4 border-t border-stone-100">
            {{ $hotels->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

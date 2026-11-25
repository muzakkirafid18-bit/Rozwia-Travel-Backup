@extends('layouts.supervisor')

@section('title', 'Maskapai - Supervisor ROZWIA')

@section('content')
<div class="space-y-6 font-serif">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <a href="{{ route('supervisor.paket') }}" class="inline-flex items-center gap-1 text-xs font-sans font-bold text-[#063B25] hover:underline">
                <x-icon name="arrow_back" class="w-4 h-4" />
                <span>Kembali ke Kelola Paket</span>
            </a>
            <h1 class="font-serif-display text-3xl font-bold text-stone-900 tracking-tight">Maskapai Penerbangan</h1>
            <p class="text-xs text-stone-600 font-sans">Data maskapai yang digunakan pada jadwal keberangkatan Haji dan Umroh.</p>
        </div>

        <a href="{{ route('supervisor.maskapai.create') }}"
           class="bg-[#063B25] hover:bg-emerald-900 text-white font-sans font-bold text-xs py-3 px-5 rounded-2xl transition shadow-sm inline-flex items-center gap-1.5 shrink-0">
            <x-icon name="add" class="w-4 h-4" />
            <span>Tambah Maskapai Baru</span>
        </a>
    </div>

    <div class="bg-white rounded-[28px] shadow-xs border border-stone-200/80 overflow-hidden font-sans max-w-4xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#063B25] text-white">
                        <th class="py-3.5 px-5 font-semibold w-20">No.</th>
                        <th class="py-3.5 px-5 font-semibold">Nama Maskapai</th>
                        <th class="py-3.5 px-5 font-semibold">Tanggal Ditambahkan</th>
                        <th class="py-3.5 px-5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-stone-700">
                    @forelse($maskapais as $index => $m)
                    <tr class="hover:bg-stone-50/80 transition">
                        <td class="py-3.5 px-5 text-stone-500">{{ $maskapais->firstItem() + $index }}</td>
                        <td class="py-3.5 px-5 font-bold text-[#063B25]">
                            <span class="inline-flex items-center gap-2">
                                <x-icon name="flight" class="w-4 h-4 text-[#B78616]" />
                                <span>{{ $m->nama_maskapai }}</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-stone-500">{{ $m->created_at ? $m->created_at->format('d/m/Y') : '-' }}</td>
                        <td class="py-3.5 px-5 text-right whitespace-nowrap space-x-3">
                            <a href="{{ route('supervisor.maskapai.edit', $m) }}" class="font-bold text-[#063B25] hover:underline">Edit</a>
                            <form action="{{ route('supervisor.maskapai.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus maskapai ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-bold text-rose-600 hover:underline cursor-pointer">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-stone-500 italic">Belum ada maskapai yang didaftarkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($maskapais->hasPages())
        <div class="p-4 border-t border-stone-100">
            {{ $maskapais->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

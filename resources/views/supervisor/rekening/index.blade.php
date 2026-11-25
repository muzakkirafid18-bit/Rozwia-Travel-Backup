@extends('layouts.supervisor')

@section('title', 'Rekening Perusahaan - Supervisor ROZWIA')

@section('content')
<div class="space-y-6 font-serif">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <a href="{{ route('supervisor.profile') }}" class="inline-flex items-center gap-1 text-xs font-sans font-bold text-[#063B25] hover:underline">
                <x-icon name="arrow_back" class="w-4 h-4" />
                <span>Kembali ke Profile Perusahaan</span>
            </a>
            <h1 class="font-serif-display text-3xl font-bold text-stone-900 tracking-tight">Daftar Rekening Resmi Travel</h1>
            <p class="text-xs text-stone-600 font-sans">Rekening ini digunakan sebagai tujuan transfer pembayaran DP &amp; Pelunasan oleh Calon Jamaah.</p>
        </div>

        <a href="{{ route('supervisor.rekening.create') }}"
           class="bg-[#063B25] hover:bg-emerald-900 text-white font-sans font-bold text-xs py-3 px-5 rounded-2xl transition shadow-sm inline-flex items-center gap-1.5 shrink-0">
            <x-icon name="add" class="w-4 h-4" />
            <span>Tambah Rekening Baru</span>
        </a>
    </div>

    <div class="bg-white rounded-[28px] shadow-xs border border-stone-200/80 overflow-hidden font-sans">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#063B25] text-white">
                        <th class="py-3.5 px-5 font-semibold">Bank</th>
                        <th class="py-3.5 px-5 font-semibold">Nomor Rekening</th>
                        <th class="py-3.5 px-5 font-semibold">Atas Nama</th>
                        <th class="py-3.5 px-5 font-semibold">Perusahaan</th>
                        <th class="py-3.5 px-5 font-semibold">Status</th>
                        <th class="py-3.5 px-5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-stone-700">
                    @forelse($rekenings as $rek)
                    <tr class="hover:bg-stone-50/80 transition">
                        <td class="py-3.5 px-5 font-bold text-[#063B25]">{{ $rek->nama_bank }}</td>
                        <td class="py-3.5 px-5 font-mono font-bold text-stone-900">{{ $rek->nomor_rekening }}</td>
                        <td class="py-3.5 px-5">{{ $rek->atas_nama }}</td>
                        <td class="py-3.5 px-5 text-stone-500">{{ $rek->nama_perusahaan }}</td>
                        <td class="py-3.5 px-5">
                            @if($rek->status === 'active')
                                <span class="bg-[#CFE5D5] text-[#063B25] px-3 py-1 rounded-full text-[10px] font-bold">Aktif</span>
                            @else
                                <span class="bg-stone-200 text-stone-600 px-3 py-1 rounded-full text-[10px] font-bold">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-right whitespace-nowrap space-x-3">
                            <a href="{{ route('supervisor.rekening.edit', $rek) }}" class="font-bold text-[#063B25] hover:underline">Edit</a>
                            <form action="{{ route('supervisor.rekening.destroy', $rek) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rekening ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-bold text-rose-600 hover:underline cursor-pointer">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-500 italic">Belum ada rekening perusahaan yang didaftarkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rekenings->hasPages())
        <div class="p-4 border-t border-stone-100">
            {{ $rekenings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.supervisor')

@section('title', 'Edit Maskapai - Supervisor ROZWIA')

@section('content')
<div class="space-y-6 max-w-xl font-sans">
    <div>
        <a href="{{ route('supervisor.maskapai.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#063B25] hover:underline">
            <x-icon name="arrow_back" class="w-4 h-4" />
            <span>Kembali ke Daftar Maskapai</span>
        </a>
        <h1 class="mt-2 font-serif-display text-3xl font-bold text-stone-900">Edit Maskapai</h1>
    </div>

    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-xs border border-stone-200/80">
        <form action="{{ route('supervisor.maskapai.update', $maskapai) }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label class="font-bold text-stone-700">Nama Maskapai Penerbangan *</label>
                <input type="text" name="nama_maskapai" value="{{ old('nama_maskapai', $maskapai->nama_maskapai) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-stone-50 border border-stone-200 focus:outline-none focus:border-[#063B25]">
            </div>

            <div class="pt-3 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-[#063B25] hover:bg-emerald-900 text-white font-bold transition cursor-pointer shadow-sm">
                    Perbarui Maskapai
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

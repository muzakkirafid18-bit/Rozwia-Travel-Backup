@extends('layouts.supervisor')

@section('title', 'Edit Rekening - Supervisor ROZWIA')

@section('content')
<div class="space-y-6 max-w-2xl font-sans">
    <div>
        <a href="{{ route('supervisor.rekening.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#063B25] hover:underline">
            <x-icon name="arrow_back" class="w-4 h-4" />
            <span>Kembali ke Daftar Rekening</span>
        </a>
        <h1 class="mt-2 font-serif-display text-3xl font-bold text-stone-900">Edit Rekening: {{ $rekening->nama_bank }}</h1>
    </div>

    <div class="bg-gradient-to-br from-white via-white to-emerald-50/50 rounded-[28px] p-6 sm:p-8 shadow-sm border border-emerald-200">
        <form action="{{ route('supervisor.rekening.update', $rekening) }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label class="font-bold text-stone-800">Nama Bank *</label>
                <input type="text" name="nama_bank" value="{{ old('nama_bank', $rekening->nama_bank) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-white border border-emerald-200 text-stone-900 focus:outline-none focus:border-emerald-600">
            </div>

            <div class="space-y-1.5">
                <label class="font-bold text-stone-800">Nomor Rekening *</label>
                <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $rekening->nomor_rekening) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-white border border-emerald-200 text-stone-900 font-mono focus:outline-none focus:border-emerald-600">
            </div>

            <div class="space-y-1.5">
                <label class="font-bold text-stone-800">Atas Nama Pemilik Rekening *</label>
                <input type="text" name="atas_nama" value="{{ old('atas_nama', $rekening->atas_nama) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-white border border-emerald-200 text-stone-900 focus:outline-none focus:border-emerald-600">
            </div>

            <div class="space-y-1.5">
                <label class="font-bold text-stone-800">Nama Perusahaan / Entitas *</label>
                <input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan', $rekening->nama_perusahaan) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-white border border-emerald-200 text-stone-900 focus:outline-none focus:border-emerald-600">
            </div>

            <div class="space-y-1.5">
                <label class="font-bold text-stone-800">Alamat Kantor Perusahaan</label>
                <textarea name="alamat_perusahaan" rows="3"
                          class="w-full px-4 py-3 rounded-xl bg-white border border-emerald-200 text-stone-900 focus:outline-none focus:border-emerald-600">{{ old('alamat_perusahaan', $rekening->alamat_perusahaan) }}</textarea>
            </div>

            <div class="space-y-1.5">
                <label class="font-bold text-stone-800">Status Rekening *</label>
                <select name="status" class="w-full px-4 py-3 rounded-xl bg-white border border-emerald-200 text-stone-900 focus:outline-none focus:border-emerald-600">
                    <option value="active" {{ old('status', $rekening->status) === 'active' ? 'selected' : '' }}>Aktif (Ditampilkan di Invoice Tagihan)</option>
                    <option value="inactive" {{ old('status', $rekening->status) === 'inactive' ? 'selected' : '' }}>Non-Aktif (Diarsipkan)</option>
                </select>
            </div>

            <div class="pt-3 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-[#063B25] hover:bg-emerald-900 text-white font-bold transition cursor-pointer shadow-sm">
                    Perbarui Rekening
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

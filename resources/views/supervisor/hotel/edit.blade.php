@extends('layouts.supervisor')

@section('title', 'Edit Hotel - Supervisor ROZWIA')

@section('content')
<div class="space-y-6 max-w-xl font-sans">
    <div>
        <a href="{{ route('supervisor.hotel.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#063B25] hover:underline">
            <x-icon name="arrow_back" class="w-4 h-4" />
            <span>Kembali ke Daftar Hotel</span>
        </a>
        <h1 class="mt-2 font-serif-display text-3xl font-bold text-stone-900">Edit Data Hotel</h1>
    </div>

    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-xs border border-stone-200/80">
        @php
            $existingImg = $hotel->foto_hotel
                ? asset($hotel->foto_hotel)
                : asset($hotel->lokasi === 'Madinah' ? 'images/crowne.jpg' : 'images/sheraton.jpg');
        @endphp
        <form action="{{ route('supervisor.hotel.update', $hotel) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs" x-data="{ previewUrl: '{{ $existingImg }}' }">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label class="font-bold text-stone-700">Nama Hotel *</label>
                <input type="text" name="nama_hotel" value="{{ old('nama_hotel', $hotel->nama_hotel) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-stone-50 border border-stone-200 focus:outline-none focus:border-[#063B25]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="font-bold text-stone-700">Lokasi Kota *</label>
                    <select name="lokasi" required class="w-full px-4 py-3 rounded-xl bg-stone-50 border border-stone-200 focus:outline-none focus:border-[#063B25]">
                        <option value="Makkah" @selected(old('lokasi', $hotel->lokasi) === 'Makkah')>Makkah</option>
                        <option value="Madinah" @selected(old('lokasi', $hotel->lokasi) === 'Madinah')>Madinah</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="font-bold text-stone-700">Bintang Hotel *</label>
                    <select name="bintang_hotel" required class="w-full px-4 py-3 rounded-xl bg-stone-50 border border-stone-200 focus:outline-none focus:border-[#063B25]">
                        @foreach([5, 4, 3] as $b)
                            <option value="{{ $b }}" @selected(old('bintang_hotel', $hotel->bintang_hotel) == $b)>Bintang {{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="font-bold text-stone-700">Alamat / Jarak ke Masjid (Opsional)</label>
                <input type="text" name="alamat_hotel" value="{{ old('alamat_hotel', $hotel->alamat_hotel) }}"
                       class="w-full px-4 py-3 rounded-xl bg-stone-50 border border-stone-200 focus:outline-none focus:border-[#063B25]">
            </div>

            <div class="space-y-1.5">
                <label class="font-bold text-stone-700">Ganti Foto Hotel (Opsional · JPG/PNG/WEBP, maks 5MB)</label>
                <input type="file" name="foto_hotel" accept="image/png,image/jpeg,image/webp"
                       @change="if ($event.target.files[0]) previewUrl = URL.createObjectURL($event.target.files[0])"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-stone-50 border border-stone-200 text-xs">
                <img :src="previewUrl" alt="Foto Hotel" class="mt-2 h-32 w-full max-w-xs rounded-xl object-cover border border-stone-200">
            </div>

            <div class="pt-3 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-[#063B25] hover:bg-emerald-900 text-white font-bold transition cursor-pointer shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

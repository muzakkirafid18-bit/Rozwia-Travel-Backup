@extends('layouts.dashboard')

@section('title', 'Profil Jamaah - ROZWIA')

@section('content')
@php
    $mask = function (?string $value, int $keep = 4): string {
        if (blank($value)) return 'Belum dilengkapi';
        $length = strlen($value);
        return $length <= $keep ? str_repeat('*', $length) : substr($value, 0, $keep) . str_repeat('*', max(0, $length - $keep));
    };
    $room = $booking?->catatan ? str_replace('Tipe Kamar: ', '', $booking->catatan) : 'Belum dialokasikan';
@endphp

<div class="min-h-screen bg-[#EAE0D0] lg:grid lg:grid-cols-[300px_minmax(0,1fr)]" 
     x-data="{ 
        editModalOpen: false, 
        currentJamaah: {
            id: null,
            nama_lengkap: '',
            nik: '',
            tempat_lahir: '',
            tanggal_lahir: '',
            passport_number: '',
            jenis_kelamin: '',
            email: '',
            nomor_telepon: '',
            alamat: ''
        },
        openEdit(jamaah) {
            let tgl = '';
            if (jamaah.tanggal_lahir) {
                tgl = typeof jamaah.tanggal_lahir === 'string' ? jamaah.tanggal_lahir.substring(0, 10) : '';
            }
            let localWa = String(jamaah.nomor_telepon || '').replace(/[^0-9]/g, '').replace(/^(?:62|0)+/, '');
            this.currentJamaah = {
                id: jamaah.id,
                nama_lengkap: jamaah.nama_lengkap || '',
                nik: jamaah.nik || '',
                tempat_lahir: jamaah.tempat_lahir || '',
                tanggal_lahir: tgl,
                passport_number: jamaah.passport_number || '',
                jenis_kelamin: jamaah.jenis_kelamin || 'Laki-laki',
                email: jamaah.email || '',
                nomor_telepon: localWa,
                alamat: jamaah.alamat || ''
            };
            this.editModalOpen = true;
        }
     }"
     x-effect="document.body.classList.toggle('overflow-hidden', editModalOpen)">
    @include('user._sidebar', ['active' => 'profil'])
    
    <main class="min-w-0 px-5 py-8 sm:px-8 lg:px-12 lg:py-12">
        <div class="mx-auto max-w-6xl space-y-7">
            <header>
                <p class="text-xs font-bold uppercase tracking-[.22em] text-[#B78616]">Portal Jamaah ROZWIA</p>
                <h1 class="mt-2 font-serif text-4xl font-bold text-[#063B25] sm:text-5xl">Profil Jamaah</h1>
            </header>

            @if(session('success'))
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-4 text-sm text-emerald-900 flex items-center gap-3 shadow-xs font-serif">
                <x-icon name="check_circle" class="w-5 h-5 text-emerald-700" />
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            @endif

            @if($errors->any())
            <div class="rounded-2xl border border-rose-300 bg-rose-50 px-5 py-4 text-sm text-rose-900 shadow-xs font-serif">
                <ul class="list-disc pl-5 space-y-1 text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- ringkasan rombongan -->
            <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-emerald-50 font-serif text-xl font-bold text-[#063B25]">
                            {{ collect(explode(' ', $booking?->nama_rombongan ?? $user->name))->map(fn ($name) => mb_substr($name, 0, 1))->take(2)->implode('') }}
                        </span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-serif text-2xl font-bold text-[#063B25]">{{ $booking?->nama_rombongan ?? $user->name }}</h2>
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold text-[#B78616] border border-amber-200">
                                    1 Akun · {{ $jamaahs->count() }} dari {{ $booking?->jumlah_jamaah ?? 1 }} Jamaah Terdaftar
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-stone-600">
                                Kode Booking: <b class="text-[#063B25] font-mono">{{ $booking?->kode_booking ?? 'Belum ada booking' }}</b>{{ $booking?->jadwal?->paket ? ' · '.$booking->jadwal->paket->nama_paket : '' }}
                            </p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 px-6 py-3 text-center">
                        <p class="text-xs text-stone-500">Alokasi Kamar</p>
                        <b class="text-[#063B25]">{{ $room }}</b>
                    </div>
                </div>
            </section>

            <!-- daftar anggota jamaah -->
            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="font-serif text-2xl font-bold text-[#063B25]">Daftar Anggota Jamaah Terdaftar</h2>
                        <p class="mt-1 text-sm text-stone-600">Silakan lengkapi data identitas setiap anggota jamaah agar manifes penerbangan dan visa dapat diterbitkan.</p>
                    </div>
                    <p class="text-xs font-bold text-stone-500 bg-stone-100 px-3 py-1.5 rounded-full border border-stone-200">
                        {{ $jamaahs->count() }} / {{ $booking?->jumlah_jamaah ?? 1 }} Jamaah
                    </p>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($jamaahs as $index => $jamaah)
                    @php
                        $isComplete = !empty($jamaah->nik) && !empty($jamaah->passport_number);
                        $isLeader   = ($index === 0);
                    @endphp
                    <article class="rounded-3xl border {{ $isLeader ? 'border-[#063B25] ring-2 ring-[#063B25]/20' : 'border-stone-200' }} bg-white p-5 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-11 w-11 place-items-center rounded-full {{ $isLeader ? 'bg-emerald-100 text-[#063B25]' : 'bg-stone-100 text-stone-700' }} font-bold text-sm">
                                        {{ collect(explode(' ', $jamaah->nama_lengkap))->map(fn ($name) => mb_substr($name, 0, 1))->take(2)->implode('') }}
                                    </span>
                                    <div>
                                        <h3 class="font-serif text-lg font-bold text-[#063B25]">{{ $jamaah->nama_lengkap }}</h3>
                                        <p class="text-xs text-stone-500 font-medium">
                                            {{ $isLeader ? 'Penanggung Jawab Akun' : 'Anggota Jamaah ke-' . ($index + 1) }}
                                        </p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $isComplete ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">
                                    <x-icon :name="$isComplete ? 'check_circle' : 'warning'" class="w-3.5 h-3.5" />
                                    <span>{{ $isComplete ? 'Lengkap' : 'Belum Lengkap' }}</span>
                                </span>
                            </div>

                            <dl class="mt-5 space-y-2.5 border-y border-stone-100 py-4 text-xs font-serif">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-stone-500">Nomor KTP (NIK)</dt>
                                    <dd class="font-bold text-[#063B25] font-mono">{{ $mask($jamaah->nik) }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-stone-500">Tempat, Tgl Lahir</dt>
                                    <dd class="font-bold text-[#063B25] text-right">
                                        {{ $jamaah->tempat_lahir ? $jamaah->tempat_lahir . ', ' : '' }}{{ $jamaah->tanggal_lahir ? $jamaah->tanggal_lahir->format('d/m/Y') : 'Belum disetel' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-stone-500">Nomor Paspor</dt>
                                    <dd class="font-bold text-[#063B25] font-mono">{{ $mask($jamaah->passport_number) }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-stone-500">Jenis Kelamin</dt>
                                    <dd class="font-bold text-[#063B25]">{{ $jamaah->jenis_kelamin ?? 'Belum disetel' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-stone-500">Email</dt>
                                    <dd class="truncate font-bold text-[#063B25]">{{ $jamaah->email ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-stone-500">No. WhatsApp</dt>
                                    <dd class="font-bold text-[#063B25]">{{ $jamaah->nomor_telepon ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-5 pt-2">
                            <button type="button" 
                                    @click="openEdit({{ json_encode($jamaah) }})" 
                                    class="w-full rounded-xl bg-amber-500 hover:bg-amber-400 text-emerald-950 px-3 py-2.5 text-center text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                                <x-icon name="edit" class="w-3.5 h-3.5" />
                                <span>{{ $isComplete ? 'Ubah Data' : 'Lengkapi Data' }}</span>
                            </button>
                        </div>
                    </article>
                    @empty
                    <article class="rounded-3xl bg-white p-8 text-center text-sm text-stone-600 shadow-sm md:col-span-2 xl:col-span-3">
                        Belum ada data jamaah pada pemesanan ini.
                    </article>
                    @endforelse
                </div>
            </section>
        </div>
    </main>

    <!-- modal ubah data jamaah -->
    <div x-show="editModalOpen" 
         x-transition.opacity 
         style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs font-serif overflow-y-auto">
        <div class="bg-white rounded-[32px] p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5 border border-stone-200 my-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <div>
                    <h3 class="text-xl font-bold text-[#063B25]">Lengkapi Data Anggota Jamaah</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Isi identitas resmi sesuai KTP dan Paspor asli.</p>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold text-lg cursor-pointer">✕</button>
            </div>

            <form :action="'{{ url('/user/jamaah') }}/' + currentJamaah.id + '/update'" method="POST" class="space-y-4 text-xs">
                @csrf
                
                <div class="space-y-1">
                    <label class="font-bold text-stone-700">Nama Lengkap (Sesuai Paspor / KTP) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" x-model="currentJamaah.nama_lengkap" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-[#063B25]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-stone-700">Nomor KTP (NIK)</label>
                        <input type="text" name="nik" x-model="currentJamaah.nik" maxlength="20" placeholder="16 digit NIK" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 font-mono focus:outline-none focus:border-[#063B25]">
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-stone-700">Nomor Paspor</label>
                        <input type="text" name="passport_number" x-model="currentJamaah.passport_number" maxlength="30" placeholder="No. Paspor RI" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 font-mono focus:outline-none focus:border-[#063B25]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-stone-700">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" x-model="currentJamaah.tempat_lahir" placeholder="Contoh: Jakarta" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-[#063B25]">
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-stone-700">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" x-model="currentJamaah.tanggal_lahir" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-[#063B25]">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-stone-700">Jenis Kelamin</label>
                    <select name="jenis_kelamin" x-model="currentJamaah.jenis_kelamin" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-[#063B25]">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-stone-700">No. WhatsApp / HP <span class="text-rose-500">*</span></label>
                        <div class="flex items-center bg-white border border-stone-300 rounded-xl overflow-hidden focus-within:border-[#063B25]">
                            <span class="px-3 py-2.5 bg-stone-100 text-[#063B25] font-bold text-xs border-r border-stone-300 select-none shrink-0">+62</span>
                            <input type="tel"
                                   name="nomor_telepon"
                                   x-model="currentJamaah.nomor_telepon"
                                   @input="currentJamaah.nomor_telepon = $event.target.value.replace(/[^0-9]/g, '').replace(/^(?:62|0)+/, '')"
                                   required
                                   minlength="8"
                                   maxlength="18"
                                   inputmode="numeric"
                                   placeholder="81234567890"
                                   class="w-full px-3 py-2.5 text-xs font-mono focus:outline-none">
                        </div>
                        <p class="text-[10px] text-stone-400">Otomatis tersimpan dalam format +62.</p>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-stone-700">Alamat Email</label>
                        <input type="email" name="email" x-model="currentJamaah.email" placeholder="email@gmail.com" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-[#063B25]">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-stone-700">Alamat Domisili</label>
                    <textarea name="alamat" x-model="currentJamaah.alamat" rows="2" placeholder="Alamat sesuai KTP..." class="w-full px-4 py-2 rounded-xl border border-stone-300 focus:outline-none focus:border-[#063B25]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-100">
                    <button type="button" @click="editModalOpen = false" class="px-5 py-2.5 rounded-xl border border-stone-300 font-bold text-stone-600 hover:bg-stone-100 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="bg-[#063B25] hover:bg-emerald-900 text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition cursor-pointer inline-flex items-center gap-1.5">
                        <x-icon name="save" class="w-4 h-4" />
                        <span>Simpan Data Jamaah</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Persyaratan Dokumen - ROZWIA')

@section('content')
@php
    $items = [
        ['key' => 'ktp', 'label' => 'e-KTP Jamaah', 'type' => 'KTP', 'icon' => 'id_card', 'help' => 'Scan atau foto KTP elektronik yang terbaca jelas.'],
        ['key' => 'kk', 'label' => 'Kartu Keluarga (KK)', 'type' => 'Kartu Keluarga', 'icon' => 'description', 'help' => 'Pastikan seluruh halaman dan data keluarga terlihat.'],
        ['key' => 'paspor', 'label' => 'Paspor Asli', 'type' => 'Paspor', 'icon' => 'passport', 'help' => 'Halaman biodata dan foto, berlaku minimal 7 bulan.'],
        ['key' => 'pas_foto', 'label' => 'Pas Foto 4x6 (Background Putih)', 'type' => 'Pas Foto 4x6', 'icon' => 'photo_camera_front', 'help' => 'Wajah tampak 80%, latar belakang putih, jilbab gelap bagi wanita, tanpa kacamata & peci.'],
        ['key' => 'buku_nikah', 'label' => 'Buku Nikah / Akta Lahir', 'type' => 'Buku Nikah / Akta Lahir', 'icon' => 'menu_book', 'help' => 'Dokumen bukti mahram (Buku Nikah bagi suami/istri atau Akta Kelahiran bagi anak/saudara).'],
    ];
    $byType = $dokumens->keyBy('tipe_dokumen');
    $needsRevision = $dokumens->first(fn ($d) => in_array($d->status, ['Ditolak', 'Perlu_Diperbaiki']));
@endphp

<div class="min-h-screen bg-[#EAE0D0] lg:grid lg:grid-cols-[300px_minmax(0,1fr)]">
    @include('user._sidebar', ['active' => 'dokumen'])
    <main class="min-w-0 px-5 py-8 sm:px-8 lg:px-12 lg:py-12">
        <div class="mx-auto max-w-6xl space-y-7">
            <header class="flex items-start justify-between gap-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.22em] text-[#B78616]">Portal Jamaah</p>
                    <h1 class="mt-2 font-serif text-4xl font-bold text-[#063B25] sm:text-5xl">Persyaratan Dokumen</h1>
                    <p class="mt-3 max-w-2xl text-sm text-stone-600">Semoga persiapan ibadah Anda dimudahkan. Pilih nama jamaah lalu lengkapi berkas perjalanan di bawah ini.</p>
                </div>
                <div class="flex flex-col items-end gap-2 shrink-0">
                    <span class="hidden rounded-full border border-amber-300 bg-amber-50 px-4 py-2 text-xs font-bold text-amber-800 sm:block">
                        {{ $dokumens->count() }} / 5 Berkas Terunggah
                    </span>
                    @if($allBookingDocsVerified ?? false)
                        <span class="rounded-full border border-emerald-300 bg-emerald-100 px-3.5 py-1 text-[11px] font-bold text-emerald-900 inline-flex items-center gap-1.5">
                            <x-icon name="check_circle" class="w-4 h-4 text-emerald-700" />
                            <span>Semua Berkas Rombongan Diverifikasi</span>
                        </span>
                    @elseif(($jamaahs->count() ?? 0) > 1)
                        <span class="rounded-full border border-stone-300 bg-white px-3.5 py-1 text-[11px] font-semibold text-stone-700">
                            Verifikasi Rombongan: {{ $verifiedMembersCount ?? 0 }} / {{ $booking?->jumlah_jamaah ?? $jamaahs->count() }} Jamaah
                        </span>
                    @endif
                </div>
            </header>

            @if(session('rombongan_popup') || session('success'))
            <div x-data="{ openPopup: true }"
                 x-show="openPopup"
                 x-transition.opacity
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
                <div @click.away="openPopup = false" class="w-full max-w-md rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-stone-200 space-y-4 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-[#063B25]">
                        <x-icon name="check_circle" class="w-8 h-8 text-emerald-700" />
                    </div>
                    <h3 class="font-serif text-2xl font-bold text-[#063B25]">Berkas Tersimpan</h3>
                    <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                        {{ session('rombongan_popup') ?? session('success') }}
                    </p>
                    <div class="pt-2">
                        <button type="button" @click="openPopup = false" class="w-full rounded-xl bg-[#063B25] hover:bg-emerald-900 py-3 text-xs font-bold text-white shadow-sm cursor-pointer">
                            Mengerti, Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @include('user._pelunasan-popup', [
                'showPelunasanPopup' => ($allBookingDocsVerified ?? false) && !($isLunas ?? false) && !session('rombongan_popup') && !session('success'),
            ])

            @if(session('error'))
            <div class="rounded-2xl border border-rose-300 bg-rose-50 px-5 py-4 text-sm text-rose-900 flex items-center gap-3 shadow-xs">
                <x-icon name="warning" class="w-5 h-5 text-rose-700" />
                <p class="font-medium">{{ session('error') }}</p>
            </div>
            @endif

            @if($needsRevision)
                <section class="rounded-3xl border border-red-200 bg-red-50 p-5 sm:flex sm:items-start sm:justify-between sm:gap-6 shadow-xs">
                    <div>
                        <b class="text-red-800">Dokumen memerlukan perbaikan</b>
                        <p class="mt-1 text-sm text-red-700">{{ $needsRevision->tipe_dokumen }}: {{ $needsRevision->admin_notes ?: 'Silakan periksa kembali dan unggah berkas yang jelas.' }}</p>
                    </div>
                    <a href="#unggah" class="mt-3 inline-block rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white sm:mt-0 hover:bg-red-700 transition">
                        Upload Ulang
                    </a>
                </section>
            @endif

            <!-- panduan unggah dokumen -->
            <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 pb-4">
                    <h2 class="font-serif text-xl font-bold text-[#063B25]">Panduan & Syarat Pengunggahan</h2>
                    <span class="text-xs text-stone-500 font-medium">Standar Kementerian Agama & Imigrasi RI</span>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-[#FBF9F3] p-4 text-sm border border-stone-100">
                        <b class="text-[#B78616]">1. Paspor Asli</b>
                        <p class="mt-2 text-stone-600 text-xs">Masa berlaku minimal 7 bulan terhitung dari tanggal keberangkatan.</p>
                    </div>
                    <div class="rounded-2xl bg-[#FBF9F3] p-4 text-sm border border-stone-100">
                        <b class="text-[#B78616]">2. Foto / Scan Jelas</b>
                        <p class="mt-2 text-stone-600 text-xs">Pastikan tidak buram, tidak terpotong, dan seluruh data terbaca jelas.</p>
                    </div>
                    <div class="rounded-2xl bg-[#FBF9F3] p-4 text-sm border border-stone-100">
                        <b class="text-[#B78616]">3. Format & Ukuran Maksimal</b>
                        <p class="mt-2 text-stone-600 text-xs">Mendukung format JPG, PNG, atau PDF dengan ukuran maksimal <strong>10 MB</strong>.</p>
                    </div>
                </div>
            </section>

            <!-- pilih anggota jamaah -->
            @if(isset($jamaahs) && $jamaahs->isNotEmpty())
            <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="font-serif text-base font-bold text-[#063B25]">Pilih Jamaah yang Ingin Dilengkapi Berkasnya</h3>
                        <p class="text-xs text-stone-500">Klik nama jamaah di bawah ini untuk melihat atau mengunggah dokumen masing-masing.</p>
                    </div>
                    <span class="text-xs font-bold text-[#B78616] bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                        Sedang Mengisi: {{ $jamaahDetail?->nama_lengkap ?? $user->name }}
                    </span>
                </div>

                <div class="flex flex-wrap gap-2.5 pt-1">
                    @foreach($jamaahs as $idx => $j)
                        @php
                            $isSelected = ($jamaahDetail && $j->id === $jamaahDetail->id);
                            $jDocCount = $j->dokumens->count();
                        @endphp
                        <a href="{{ route('user.dokumen', ['jamaah_id' => $j->id]) }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition {{ $isSelected ? 'bg-[#063B25] text-white shadow-md ring-2 ring-[#063B25]/20' : 'bg-stone-50 text-stone-700 border border-stone-200 hover:bg-stone-100' }}">
                            <x-icon :name="$idx === 0 ? 'stars' : 'person'" class="w-4 h-4" />
                            <span>{{ $j->nama_lengkap }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $isSelected ? 'bg-amber-400 text-emerald-950' : 'bg-stone-200 text-stone-700' }}">
                                {{ $jDocCount }}/5
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- form unggah berkas -->
            <form id="unggah" action="{{ route('user.dokumen.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ totalStaged: 0 }">
                @csrf
                <input type="hidden" name="jamaah_detail_id" value="{{ $jamaahDetail?->id }}">

                <div class="space-y-1">
                    <h2 class="font-serif text-2xl font-bold text-[#063B25]">
                        Daftar Dokumen: <span class="text-[#B78616]">{{ $jamaahDetail?->nama_lengkap ?? $user->name }}</span>
                    </h2>
                    <p class="text-sm text-stone-600">Lengkapi berkas di bawah ini, lalu klik tombol <strong>Simpan Berkas</strong> di bawah daftar kartu dokumen.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3 items-stretch">
                    @foreach($items as $item) 
                    @php($doc = $byType->get($item['type']))
                    
                    <article x-data="{ 
                                fileName: '', 
                                fileSize: '', 
                                isPicked: false,
                                onFilePicked(e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        this.fileName = file.name;
                                        this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                                        if (!this.isPicked) {
                                            totalStaged++;
                                            this.isPicked = true;
                                        }
                                    }
                                }
                             }"
                             :class="isPicked ? 'border-2 border-emerald-500 ring-4 ring-emerald-100 bg-emerald-50/20' : '{{ $doc && in_array($doc->status, ['Ditolak', 'Perlu_Diperbaiki']) ? 'border-red-300' : 'border-stone-200' }} bg-white'"
                             class="rounded-3xl border p-5 shadow-sm flex flex-col justify-between transition-all duration-200">
                        
                        <div>
                            <!-- header kartu berkas -->
                            <div class="flex items-start justify-between gap-3">
                                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-50 text-[#063B25]">
                                    <x-icon :name="$item['icon']" class="w-6 h-6" />
                                </span>

                                @if($doc)
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[10px] font-bold {{ $doc->status === 'Diverifikasi' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : (in_array($doc->status, ['Ditolak','Perlu_Diperbaiki']) ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}">
                                        <x-icon :name="$doc->status === 'Diverifikasi' ? 'check_circle' : ($doc->status === 'Menunggu_Verifikasi' ? 'schedule' : 'warning')" class="w-3.5 h-3.5" />
                                        {{ str_replace('_', ' ', $doc->status) }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-stone-100 border border-stone-200 px-3 py-1 text-[10px] font-bold text-stone-600">
                                        Belum Unggah
                                    </span>
                                @endif
                            </div>

                            <!-- judul & keterangan berkas -->
                            <h3 class="mt-4 font-serif text-xl font-bold text-[#063B25]">{{ $item['label'] }}</h3>
                            <p class="mt-1.5 text-xs leading-relaxed text-stone-600">{{ $doc?->admin_notes ?: $item['help'] }}</p>

                            @if($item['key'] === 'paspor')
                                <div class="mt-3 space-y-1 font-sans">
                                    <label class="block text-[11px] font-bold text-stone-700">Nomor Paspor * <span class="font-normal text-rose-600">(Wajib diisi)</span></label>
                                    <input type="text"
                                           name="passport_number"
                                           required
                                           minlength="5"
                                           value="{{ old('passport_number', $jamaahDetail?->passport_number) }}"
                                           placeholder="Contoh: C1234567"
                                           class="w-full rounded-xl border border-stone-300 bg-stone-50 px-3.5 py-2 text-xs font-mono font-semibold text-stone-900 focus:border-[#063B25] focus:bg-white focus:outline-none">
                                    @error('passport_number')
                                        <p class="text-[11px] font-bold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <!-- info berkas tersimpan -->
                            @if($doc)
                            <?php
                                $savedSizeText = null;
                                if ($doc->file_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($doc->file_path)) {
                                    $bytes = \Illuminate\Support\Facades\Storage::disk('private')->size($doc->file_path);
                                    $mb = number_format($bytes / 1048576, 2);
                                    $kb = round($bytes / 1024);
                                    $savedSizeText = $bytes >= 1048576 ? "{$mb} MB" : "{$kb} KB ({$mb} MB)";
                                }
                            ?>
                            <div class="mt-3.5 p-3 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-xs flex items-center justify-between">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <x-icon name="description" class="w-4 h-4 text-[#063B25] shrink-0" />
                                    <div class="overflow-hidden">
                                        <span class="font-bold text-emerald-950 block truncate">Berkas Tersimpan</span>
                                        <span class="text-[10px] text-emerald-700 block">
                                            {{ $doc->updated_at->timezone('Asia/Jakarta')->format('d/m/Y, H:i') }} WIB
                                            @if($savedSizeText)
                                                · <strong class="text-emerald-900">{{ $savedSizeText }}</strong>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <span class="bg-emerald-200 text-emerald-900 text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0">Tersedia</span>
                            </div>
                            @endif

                            <!-- info file yang dipilih -->
                            <div x-show="isPicked" x-cloak class="mt-3.5 p-3 rounded-2xl bg-amber-50 border border-amber-300 text-xs space-y-1 animate-fade-in">
                                <div class="flex items-center justify-between text-amber-950 font-bold">
                                    <span class="flex items-center gap-1.5 truncate">
                                        <x-icon name="upload_file" class="w-4 h-4 text-amber-700 shrink-0" />
                                        <span x-text="fileName" class="truncate max-w-[130px]"></span>
                                    </span>
                                    <span class="text-[10px] bg-amber-200 px-2 py-0.5 rounded-full shrink-0" x-text="fileSize"></span>
                                </div>
                                <p class="text-[10px] text-amber-800 font-semibold">Siap diunggah! Jangan lupa klik "Simpan Berkas" di bawah.</p>
                            </div>
                        </div>

                        <!-- tombol pilih file -->
                        <div class="mt-5 pt-3 border-t border-stone-100">
                            <label :class="isPicked 
                                            ? 'bg-emerald-800 hover:bg-emerald-900 text-white ring-2 ring-emerald-500' 
                                            : '{{ $doc ? 'bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-300' : 'bg-[#063B25] hover:bg-emerald-900 text-white shadow-xs' }}'"
                                   class="block cursor-pointer rounded-xl px-4 py-3 text-center text-xs font-bold transition shadow-xs">
                                
                                <span x-show="!isPicked" class="inline-flex items-center justify-center gap-1.5">
                                    <x-icon name="upload_file" class="w-4 h-4" />
                                    <span>{{ $doc ? 'Ganti Berkas Lain' : 'Unggah Berkas' }}</span>
                                </span>
                                <span x-show="isPicked" x-cloak class="flex items-center justify-center gap-1.5">
                                    <x-icon name="check_plain" class="w-4 h-4" /> <span>File Terpilih (Klik utk Ganti)</span>
                                </span>
                                
                                <input class="hidden" 
                                       type="file" 
                                       name="{{ $item['key'] }}" 
                                       accept=".jpg,.jpeg,.png,.pdf" 
                                       @change="onFilePicked($event)">
                            </label>

                            <p class="mt-2 text-[10px] text-stone-500 text-center">Format: JPG, PNG, PDF (Maks. 10MB)</p>

                            @error($item['key'])
                                <p class="mt-2 text-xs font-bold text-red-600 text-center">{{ $message }}</p>
                            @enderror
                        </div>

                    </article>
                    @endforeach
                </div>

                <!-- tombol simpan berkas -->
                <div class="bg-white rounded-3xl border border-stone-200 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-stone-600">
                        <strong class="block text-[#063B25] text-sm font-serif">Konfirmasi Pengiriman Berkas</strong>
                        <span>Pastikan seluruh berkas dan Nomor Paspor sudah benar sebelum menyimpan.</span>
                    </div>
                    <button type="submit"
                            :class="totalStaged > 0 ? 'bg-amber-500 hover:bg-amber-400 text-emerald-950 ring-4 ring-amber-200 font-extrabold' : 'bg-[#063B25] hover:bg-emerald-900 text-white'"
                            class="rounded-xl px-7 py-3.5 text-sm font-bold transition shadow-md cursor-pointer inline-flex items-center justify-center gap-2 shrink-0">
                        <x-icon name="save" class="w-4 h-4" />
                        <span x-text="totalStaged > 0 ? 'Simpan (' + totalStaged + ' Berkas Dipilih) →' : 'Simpan Berkas'">Simpan Berkas</span>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection

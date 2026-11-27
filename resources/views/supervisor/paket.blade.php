@extends('layouts.supervisor')

@section('title', 'Kelola Paket Haji & Umroh - Supervisor ROZWIA')

@section('content')
@php
    $hotelsMakkah = $hotels->where('lokasi', 'Makkah')->values();
    $hotelsMadinah = $hotels->where('lokasi', 'Madinah')->values();
    $hotelsImageMap = $hotels->mapWithKeys(function ($h) {
        $url = $h->foto_hotel
            ? asset($h->foto_hotel)
            : asset($h->lokasi === 'Madinah' ? 'images/crowne.jpg' : 'images/sheraton.jpg');
        return [$h->id => $url];
    });

    $paketsPayload = $pakets->map(function ($p) use ($maskapais, $hotelsMakkah, $hotelsMadinah) {
        $firstJadwal = $p->jadwals->first();
        $durasi = ($firstJadwal && $firstJadwal->tanggal_keberangkatan && $firstJadwal->tanggal_pulang)
            ? max(1, (int) $firstJadwal->tanggal_keberangkatan->diffInDays($firstJadwal->tanggal_pulang))
            : 9;

        $kamars = $p->paketTipeKamars;
        $makkahKamars = $kamars->filter(fn($k) => strtolower($k->hotel?->lokasi ?? '') === 'makkah');
        $madinahKamars = $kamars->filter(fn($k) => strtolower($k->hotel?->lokasi ?? '') === 'madinah');

        $makkahHotelId = $makkahKamars->first()?->id_hotel ?? ($hotelsMakkah->first()?->id ?? '');
        $madinahHotelId = $madinahKamars->first()?->id_hotel ?? ($hotelsMadinah->first()?->id ?? '');

        $getAllInHarga = function (string $tipe, int $fallback) use ($kamars, $p) {
            $matched = $kamars->filter(fn ($k) => strtolower(trim((string) ($k->tipeKamar?->nama ?? ''))) === $tipe);
            if ($matched->isEmpty()) {
                return $fallback;
            }
            $first = (float) $matched->first()->harga;
            $base  = (float) $p->harga_dasar;
            return (int) ($first >= $base ? $first : ($base + $matched->sum('harga')));
        };

        $hargaQuad   = $p->quadPrice() > 0 ? (int) $p->quadPrice() : $getAllInHarga('quad', (int) $p->harga_dasar);
        $hargaTriple = $getAllInHarga('triple', $hargaQuad + 3000000);
        $hargaDouble = $getAllInHarga('double', $hargaQuad + 7000000);

        $rawImg = $p->gambar_utama;
        $isHaji = strtolower($p->kategori) === 'haji';
        $fallbackImg = $isHaji ? asset('images/paket haji.jpg') : asset('images/pilihan paket.jpg');
        if (empty($rawImg) || str_contains($rawImg, 'images.unsplash.com')) {
            $imgUrl = $fallbackImg;
        } elseif (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) {
            $imgUrl = $rawImg;
        } else {
            $imgUrl = asset(ltrim($rawImg, '/'));
        }

        return [
            'id'               => $p->id,
            'nama_paket'       => $p->nama_paket,
            'kategori'         => $p->kategori === 'Haji' ? 'Haji' : 'Umrah',
            'id_jenis_layanan' => $p->id_jenis_layanan,
            'harga'            => (int) $hargaQuad,
            'durasi'           => $durasi,
            'deskripsi'        => $p->deskripsi ?? '',
            'fasilitas'        => is_array($p->fasilitas) ? array_values($p->fasilitas) : [],
            'status'           => $p->status === 'inactive' ? 'inactive' : 'active',
            'image_url'        => $imgUrl,
            'tanggal_mulai'    => $firstJadwal?->tanggal_keberangkatan?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d'),
            'tanggal_pulang'   => $firstJadwal?->tanggal_pulang?->format('Y-m-d') ?? now()->addDays(39)->format('Y-m-d'),
            'id_maskapai'      => $firstJadwal?->id_maskapai ?? ($maskapais->first()?->id ?? ''),
            'tempat_kumpul'    => $firstJadwal?->tempat_kumpul ?? '',
            'tanggal_kumpul'   => $firstJadwal?->tanggal_kumpul?->format('Y-m-d\TH:i') ?? '',
            'kuota'            => $firstJadwal?->kuota ?? 40,
            'id_hotel_makkah'  => $makkahHotelId,
            'id_hotel_madinah' => $madinahHotelId,
            'harga_quad'       => $hargaQuad,
            'harga_triple'     => $hargaTriple,
            'harga_double'     => $hargaDouble,
        ];
    })->keyBy('id');
@endphp

<div x-data="{
        openModal: false,
        filterKategori: 'semua',
        modalStep: 1,
        mode: 'create',
        editId: null,
        formAction: '{{ route('supervisor.paket.store') }}',
        paketsMap: {{ Js::from($paketsPayload) }},
        hotelsImageMap: {{ Js::from($hotelsImageMap) }},
        getHotelImage(id, lokasi) {
            if (id && this.hotelsImageMap[id]) return this.hotelsImageMap[id];
            return lokasi === 'Madinah' ? '{{ asset('images/crowne.jpg') }}' : '{{ asset('images/sheraton.jpg') }}';
        },
        hitungDurasi() {
            if (!this.form.tanggal_mulai || !this.form.tanggal_pulang) return null;
            const start = new Date(this.form.tanggal_mulai + 'T00:00:00');
            const end = new Date(this.form.tanggal_pulang + 'T00:00:00');
            const diff = Math.round((end - start) / 86400000);
            return diff >= 0 ? Math.max(1, diff) : null;
        },
        form: {
            nama_paket: '',
            kategori: 'Umrah',
            id_jenis_layanan: '{{ $jenisLayanans->first()?->id ?? '' }}',
            durasi: 9,
            deskripsi: 'Perjalanan ibadah aman, nyaman, dan terpercaya bersama bimbingan muthawif bersertifikasi ROZWIA.',
            status: 'active',
            tanggal_mulai: '{{ now()->addDays(30)->format('Y-m-d') }}',
            tanggal_pulang: '{{ now()->addDays(39)->format('Y-m-d') }}',
            id_maskapai: '{{ $maskapais->first()?->id ?? '' }}',
            tempat_kumpul: '',
            tanggal_kumpul: '',
            kuota: 40,
            id_hotel_makkah: '{{ $hotelsMakkah->first()?->id ?? '' }}',
            id_hotel_madinah: '{{ $hotelsMadinah->first()?->id ?? '' }}',
            harga_quad: 35000000,
            harga_triple: 38000000,
            harga_double: 42000000,
        },
        selectedFasilitas: [],
        newFasilitas: '',
        previewImage: '',
        hapusGambar: false,
        formError: '',
        formatHargaInput(val) {
            const digits = String(val ?? '').replace(/[^\d]/g, '');
            if (!digits) return '';
            return Number(digits).toLocaleString('id-ID');
        },
        updateHargaField(field, event) {
            const digits = String(event.target.value || '').replace(/[^\d]/g, '');
            this.form[field] = digits ? parseInt(digits, 10) : 0;
            if (field === 'harga_quad') {
                this.form.harga = this.form[field];
            }
            event.target.value = digits ? Number(digits).toLocaleString('id-ID') : '';
        },
        addFasilitas(val) {
            const text = (val !== undefined ? String(val) : String(this.newFasilitas || '')).trim();
            if (!text) return;
            if (!this.selectedFasilitas.some(f => f.toLowerCase() === text.toLowerCase())) {
                this.selectedFasilitas.push(text);
            }
            if (val === undefined) this.newFasilitas = '';
        },
        removeFasilitas(idx) {
            this.selectedFasilitas.splice(idx, 1);
        },
        toggleFasilitas(item) {
            if (this.selectedFasilitas.includes(item)) {
                this.selectedFasilitas = this.selectedFasilitas.filter(i => i !== item);
            } else {
                this.selectedFasilitas.push(item);
            }
        },
        openCreateModal() {
            this.mode = 'create';
            this.editId = null;
            this.formAction = '{{ route('supervisor.paket.store') }}';
            this.modalStep = 1;
            this.hapusGambar = false;
            this.previewImage = '';
            this.formError = '';
            this.selectedFasilitas = [];
            this.newFasilitas = '';
            if (this.$refs.gambarInput) this.$refs.gambarInput.value = '';
            this.form = {
                nama_paket: '',
                kategori: 'Umrah',
                id_jenis_layanan: '{{ $jenisLayanans->first()?->id ?? '' }}',
                durasi: 9,
                deskripsi: 'Perjalanan ibadah aman, nyaman, dan terpercaya bersama bimbingan muthawif bersertifikasi ROZWIA.',
                status: 'active',
                tanggal_mulai: '{{ now()->addDays(30)->format('Y-m-d') }}',
                tanggal_pulang: '{{ now()->addDays(39)->format('Y-m-d') }}',
                id_maskapai: '{{ $maskapais->first()?->id ?? '' }}',
                tempat_kumpul: '',
                tanggal_kumpul: '',
                kuota: 40,
                id_hotel_makkah: '{{ $hotelsMakkah->first()?->id ?? '' }}',
                id_hotel_madinah: '{{ $hotelsMadinah->first()?->id ?? '' }}',
                harga_quad: 35000000,
                harga_triple: 38000000,
                harga_double: 42000000,
                harga: 35000000,
            };
            this.openModal = true;
        },
        openEditModal(id) {
            const p = this.paketsMap[id];
            if (!p) return;
            this.mode = 'edit';
            this.editId = id;
            this.formAction = '{{ url('/supervisor/paket') }}/' + id;
            this.modalStep = 1;
            this.hapusGambar = false;
            this.formError = '';
            this.previewImage = p.image_url || '';
            this.selectedFasilitas = Array.isArray(p.fasilitas) ? [...p.fasilitas] : [];
            this.newFasilitas = '';
            if (this.$refs.gambarInput) this.$refs.gambarInput.value = '';
            this.form = Object.assign({}, p);
            this.openModal = true;
        },
        goToStep2() {
            this.formError = '';
            if (!this.form.nama_paket || String(this.form.nama_paket).trim().length < 3) {
                this.formError = 'Mohon isi Nama Paket minimal 3 karakter sebelum melanjutkan.';
                return;
            }
            this.modalStep = 2;
        },
        goToStep3() {
            this.formError = '';
            if (this.form.tanggal_mulai && this.form.tanggal_pulang && this.form.tanggal_pulang < this.form.tanggal_mulai) {
                this.formError = 'Tanggal pulang tidak boleh lebih awal dari tanggal mulai.';
                return;
            }
            this.modalStep = 3;
        },
        onFilePicked(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.hapusGambar = false;
            this.previewImage = URL.createObjectURL(file);
        },
        clearImage() {
            this.previewImage = '';
            this.hapusGambar = true;
            if (this.$refs.gambarInput) this.$refs.gambarInput.value = '';
        },
        submitPaket(e) {
            this.formError = '';
            if (!this.form.nama_paket || String(this.form.nama_paket).trim().length < 3) {
                this.formError = 'Nama paket wajib diisi minimal 3 karakter (Langkah 1).';
                this.modalStep = 1;
                return;
            }
            this.form.harga = Number(this.form.harga_quad || this.form.harga || 0);
            if (!this.form.harga_quad || Number(this.form.harga_quad) < 100000) {
                this.formError = 'Harga Quad paket wajib diisi minimal Rp 100.000 (Langkah 3).';
                this.modalStep = 3;
                return;
            }
            if (this.form.tanggal_mulai && this.form.tanggal_pulang && this.form.tanggal_pulang < this.form.tanggal_mulai) {
                this.formError = 'Tanggal pulang tidak boleh lebih awal dari tanggal mulai (Langkah 2).';
                this.modalStep = 2;
                return;
            }
            if (!this.form.deskripsi || !String(this.form.deskripsi).trim()) {
                this.form.deskripsi = 'Perjalanan ibadah aman, nyaman, dan terpercaya bersama bimbingan muthawif bersertifikasi ROZWIA.';
            }
            e.target.submit();
        }
     }"
     x-effect="document.body.classList.toggle('overflow-hidden', openModal)"
     @keydown.escape.window="openModal = false"
     class="space-y-6 font-serif">

    <!-- header & tombol tambah paket -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="bg-white rounded-2xl px-6 py-4 border border-stone-200/80 shadow-xs space-y-0.5">
            <h1 class="font-serif-display text-3xl font-bold text-stone-900 tracking-tight">Kelola Paket Haji &amp; Umroh</h1>
            <p class="text-xs text-stone-600 font-sans">
                Kelola paket haji dan umroh yang tersedia di travel anda
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 font-sans">
            <a href="{{ route('supervisor.hotel.index') }}"
               class="bg-white hover:bg-stone-50 text-[#063B25] border border-stone-300 font-bold text-xs py-3 px-5 rounded-2xl transition shadow-xs inline-flex items-center gap-1.5">
                <x-icon name="hotel" class="w-4 h-4" />
                <span>Hotel</span>
            </a>

            <a href="{{ route('supervisor.maskapai.index') }}"
               class="bg-white hover:bg-stone-50 text-[#063B25] border border-stone-300 font-bold text-xs py-3 px-5 rounded-2xl transition shadow-xs inline-flex items-center gap-1.5">
                <x-icon name="flight" class="w-4 h-4" />
                <span>Maskapai</span>
            </a>

            <button type="button"
                    @click="openCreateModal()"
                    class="bg-[#063B25] hover:bg-emerald-900 text-white font-bold text-xs py-3 px-6 rounded-2xl transition cursor-pointer shadow-sm inline-flex items-center gap-2">
                <span>Tambah Paket</span>
                <x-icon name="add_box" class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- filter kategori paket -->
    <div class="flex items-center gap-2 font-sans">
        <button type="button"
                @click="filterKategori = 'semua'"
                :class="filterKategori === 'semua'
                    ? 'bg-[#063B25] text-white shadow-xs'
                    : 'bg-white text-stone-700 border border-stone-300 hover:bg-stone-50'"
                class="px-5 py-2.5 rounded-2xl text-xs font-bold transition cursor-pointer">
            Semua Paket
        </button>
        <button type="button"
                @click="filterKategori = 'Umrah'"
                :class="filterKategori === 'Umrah'
                    ? 'bg-[#063B25] text-white shadow-xs'
                    : 'bg-white text-stone-700 border border-stone-300 hover:bg-stone-50'"
                class="px-5 py-2.5 rounded-2xl text-xs font-bold transition cursor-pointer">
            Paket Umroh
        </button>
        <button type="button"
                @click="filterKategori = 'Haji'"
                :class="filterKategori === 'Haji'
                    ? 'bg-amber-600 text-white shadow-xs'
                    : 'bg-white text-stone-700 border border-stone-300 hover:bg-stone-50'"
                class="px-5 py-2.5 rounded-2xl text-xs font-bold transition cursor-pointer">
            Paket Haji
        </button>
    </div>

    <!-- grid daftar paket -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 font-sans">
        @forelse($pakets as $p)
        @php
            $firstJadwal = $p->jadwals->first();
            $kuotaTotal = $firstJadwal ? (int) $firstJadwal->kuota : 40;
            $terisi = $firstJadwal
                ? (int) $firstJadwal->bookings->whereNotIn('status_booking', ['Dibatalkan', 'Ditolak'])->sum('jumlah_jamaah')
                : 0;
            $persen = $kuotaTotal > 0 ? min(100, round(($terisi / $kuotaTotal) * 100)) : 0;
            $isHaji = strtolower($p->kategori) === 'haji';
            $isAktif = $p->status === 'active';
            $cardImg = $paketsPayload[$p->id]['image_url'] ?? asset('images/pilihan paket.jpg');
        @endphp
        <div x-show="filterKategori === 'semua' || filterKategori === '{{ $p->kategori }}'"
             x-transition
             class="bg-white rounded-[32px] overflow-hidden shadow-sm border border-stone-200 flex flex-col justify-between transition hover:shadow-md">
            <!-- header kartu paket -->
            <div class="bg-[#0B3B24] text-white p-6 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="bg-[#E5D3B3] text-[#0B3B24] text-[10px] font-bold px-3 py-1 rounded-full uppercase">
                        {{ $p->kategori }}
                    </span>
                    <span class="{{ $isAktif ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border-rose-500/40' }} border text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $isAktif ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                        {{ $isAktif ? 'Aktif' : 'Non Aktif' }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold font-serif leading-tight line-clamp-1">{{ $p->nama_paket }}</h3>
                    <p class="text-amber-300 font-bold text-sm mt-0.5 tabular-nums">{{ \App\Helpers\RozwiaHelper::formatRupiah($p->quadPrice()) }}</p>
                </div>
            </div>

            <!-- isi kartu paket -->
            <div class="p-6 space-y-4 flex-1 flex flex-col justify-between">
                <div class="flex flex-wrap gap-2 text-[12px] text-stone-500">
                    <span class="bg-stone-100 px-3 py-1 rounded-lg inline-flex items-center gap-1.5">
                        <x-icon name="timer" class="w-4 h-4 text-[#B78616]" />
                        <span>{{ $firstJadwal && $firstJadwal->tanggal_keberangkatan && $firstJadwal->tanggal_pulang ? ($firstJadwal->tanggal_keberangkatan->diffInDays($firstJadwal->tanggal_pulang) . ' Hari') : '9 Hari' }}</span>
                    </span>
                    <span class="bg-stone-100 px-3 py-1 rounded-lg inline-flex items-center gap-1.5">
                        <x-icon name="calendar_month" class="w-4 h-4 text-[#0B3B24]" />
                        <span>{{ \App\Helpers\RozwiaHelper::formatDate($firstJadwal?->tanggal_keberangkatan, 'd/m/Y', 'Menunggu jadwal') }}</span>
                    </span>
                </div>

                <p class="text-xs text-stone-600 leading-relaxed font-serif line-clamp-2">
                    {{ $p->deskripsi }}
                </p>

                <div class="flex flex-wrap gap-1.5 text-[10px]">
                    @php
                        $paketFasilitas = is_array($p->fasilitas) && count($p->fasilitas) > 0
                            ? $p->fasilitas
                            : ['Transportasi', 'Konsumsi', 'Hotel', ($isHaji ? 'Visa Haji' : 'Visa Umrah')];
                    @endphp
                    @foreach($paketFasilitas as $fas)
                        <span class="bg-[#F4EFE6] text-stone-700 px-2.5 py-1 rounded-md font-medium">{{ $fas }}</span>
                    @endforeach
                </div>

                <!-- progres kuota jamaah -->
                <div class="space-y-1.5 pt-2 border-t border-stone-100 text-xs">
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="text-stone-500 inline-flex items-center gap-1">
                            <x-icon name="group" class="w-4 h-4" />
                            <span>{{ $terisi }}/{{ $kuotaTotal }} Jamaah</span>
                        </span>
                        <strong class="{{ $isHaji ? 'text-amber-800' : 'text-[#063B25]' }} font-bold">{{ $persen }}%</strong>
                    </div>
                    <div class="w-full bg-stone-100 h-2 rounded-full overflow-hidden">
                        <div class="{{ $isHaji ? 'bg-amber-600' : 'bg-[#063B25]' }} h-full rounded-full transition-all" style="width: {{ $persen }}%"></div>
                    </div>
                </div>

                <!-- tombol aksi paket -->
                <div class="pt-3 border-t border-stone-100 flex items-center justify-between gap-2 text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <button type="button"
                                @click="openEditModal({{ $p->id }})"
                                class="px-3 py-1.5 rounded-xl bg-[#063B25] hover:bg-emerald-900 text-white font-bold transition cursor-pointer inline-flex items-center gap-1">
                            <x-icon name="edit" class="w-3.5 h-3.5" />
                            <span>Edit</span>
                        </button>

                        <form action="{{ route('supervisor.paket.toggle', $p->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-xl border font-bold transition cursor-pointer {{ $isAktif ? 'border-stone-300 text-stone-700 hover:bg-stone-100' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                {{ $isAktif ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>

                    <form action="{{ route('supervisor.paket.delete', $p->id) }}" method="POST" onsubmit="return confirm('Hapus paket {{ addslashes($p->nama_paket) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold cursor-pointer inline-flex items-center gap-0.5">
                            <x-icon name="delete" class="w-4 h-4" />
                            <span>Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-3xl p-10 text-center text-stone-500 text-sm">
            Belum ada paket terdaftar. Klik tombol "Tambah Paket" untuk membuat paket baru.
        </div>
        @endforelse
    </div>

    <!-- modal tambah / edit paket -->
    <div x-show="openModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
         @click.self="openModal = false">

        <div class="bg-white rounded-[28px] w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl border border-stone-200 overflow-hidden"
             @click.stop>

            <!-- header modal paket -->
            <div class="px-6 pt-5 pb-4 border-b border-stone-100 space-y-4 shrink-0 bg-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-serif-display text-2xl font-bold text-[#063B25]" x-text="mode === 'edit' ? 'Edit Paket Perjalanan' : 'Tambah Paket'">Tambah Paket</h2>
                        <p class="text-xs text-stone-500 font-sans mt-0.5">Lengkapi informasi paket haji atau umroh di bawah ini.</p>
                    </div>
                    <button type="button" @click="openModal = false"
                            class="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 hover:text-stone-900 font-bold text-base flex items-center justify-center transition cursor-pointer shrink-0"
                            title="Tutup">✕</button>
                </div>

                <!-- navigasi tahap form -->
                <div class="flex items-center justify-between px-4 sm:px-10 font-sans text-xs">
                    <button type="button" @click="modalStep = 1" class="flex flex-col items-center gap-1 cursor-pointer">
                        <span :class="modalStep === 1 ? 'bg-[#063B25] text-white' : 'bg-stone-100 text-stone-600'"
                              class="w-8 h-8 rounded-full flex items-center justify-center font-bold border border-stone-300">1</span>
                        <span :class="modalStep === 1 ? 'text-[#063B25] font-bold' : 'text-stone-400'">Informasi Paket</span>
                    </button>
                    <div class="flex-1 h-px bg-stone-200 mx-2 -mt-4"></div>
                    <button type="button" @click="goToStep2()" class="flex flex-col items-center gap-1 cursor-pointer">
                        <span :class="modalStep === 2 ? 'bg-[#063B25] text-white' : 'bg-stone-100 text-stone-600'"
                              class="w-8 h-8 rounded-full flex items-center justify-center font-bold border border-stone-300">2</span>
                        <span :class="modalStep === 2 ? 'text-[#063B25] font-bold' : 'text-stone-400'">Jadwal</span>
                    </button>
                    <div class="flex-1 h-px bg-stone-200 mx-2 -mt-4"></div>
                    <button type="button" @click="goToStep3()" class="flex flex-col items-center gap-1 cursor-pointer">
                        <span :class="modalStep === 3 ? 'bg-[#063B25] text-white' : 'bg-stone-100 text-stone-600'"
                              class="w-8 h-8 rounded-full flex items-center justify-center font-bold border border-stone-300">3</span>
                        <span :class="modalStep === 3 ? 'text-[#063B25] font-bold' : 'text-stone-400'">Hotel</span>
                    </button>
                </div>

                <div x-show="formError" x-cloak class="rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-3.5 py-2.5 text-xs font-sans font-semibold flex items-center justify-between gap-2 shadow-xs">
                    <div class="flex items-center gap-2">
                        <x-icon name="error" class="w-4 h-4 text-rose-600" />
                        <span x-text="formError"></span>
                    </div>
                    <button type="button" @click="formError = ''" class="text-rose-600 hover:text-rose-900 font-bold px-1.5 py-0.5 rounded cursor-pointer" title="Tutup">
                        <x-icon name="close" class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- isi form paket -->
            <form :action="formAction" method="POST" enctype="multipart/form-data" novalidate @submit.prevent="submitPaket($event)" class="font-sans text-xs space-y-5 p-6 overflow-y-auto flex-1">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="hapus_gambar" :value="hapusGambar ? 1 : 0">

                <!-- step 1: info paket -->
                <div x-show="modalStep === 1" class="space-y-4">
                    <div class="space-y-1">
                        <label class="font-bold text-stone-800">Nama Paket *</label>
                        <input type="text" name="nama_paket" x-model="form.nama_paket" required placeholder="Contoh: Umroh Plus Turki 12 Hari"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-1 focus:ring-[#063B25]">
                        <p class="text-[10px] text-stone-500 font-normal">Minimal 3 karakter</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer"
                               :class="form.kategori === 'Umrah' ? 'bg-[#CFE5D5] border-[#063B25] font-bold' : 'bg-white border-stone-300'">
                            <span>Umrah</span>
                            <input type="radio" name="kategori" value="Umrah" x-model="form.kategori" class="accent-[#063B25]">
                        </label>
                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer"
                               :class="form.kategori === 'Haji' ? 'bg-[#CFE5D5] border-[#063B25] font-bold' : 'bg-white border-stone-300'">
                            <span>Haji</span>
                            <input type="radio" name="kategori" value="Haji" x-model="form.kategori" class="accent-[#063B25]">
                        </label>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-stone-800">Jenis Layanan</label>
                        <select name="id_jenis_layanan" x-model="form.id_jenis_layanan" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white">
                            @foreach($jenisLayanans as $jl)
                                <option value="{{ $jl->id }}">{{ $jl->nama_pelayanan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-stone-800">Deskripsi Paket</label>
                            <span class="text-[10px] text-stone-400 font-sans" x-text="(form.deskripsi ? form.deskripsi.length : 0) + ' karakter'"></span>
                        </div>
                        <textarea name="deskripsi" x-model="form.deskripsi" rows="3" placeholder="Tuliskan gambaran dan keunggulan paket ini..."
                                  class="w-full px-4 py-2 rounded-xl border border-stone-300 leading-relaxed"></textarea>
                    </div>

                    <div class="space-y-2.5">
                        <label class="font-bold text-stone-800 block">Fasilitas Paket <span class="font-normal text-stone-500">(ketik lalu tekan Enter / Tambah)</span></label>

                        <template x-for="(tag, idx) in selectedFasilitas" :key="tag + '-' + idx">
                            <input type="hidden" name="fasilitas[]" :value="tag">
                        </template>

                        <div class="flex items-center gap-2">
                            <input type="text"
                                   x-model="newFasilitas"
                                   @keydown.enter.prevent="addFasilitas()"
                                   placeholder="Contoh: Tiket Pesawat, Hotel Bintang 5, Visa..."
                                   class="flex-1 px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-1 focus:ring-[#063B25]">
                            <button type="button"
                                    @click="addFasilitas()"
                                    class="px-4 py-2.5 rounded-xl bg-[#063B25] hover:bg-emerald-900 text-white font-bold text-xs inline-flex items-center gap-1 cursor-pointer shrink-0">
                                <x-icon name="add" class="w-4 h-4" />
                                <span>Tambah</span>
                            </button>
                        </div>

                        <div x-show="selectedFasilitas.length > 0" class="flex flex-wrap gap-2 pt-1">
                            <template x-for="(tag, idx) in selectedFasilitas" :key="'selected-' + idx">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#CFE5D5] text-[#063B25] border border-[#063B25]/30 text-[11px] font-bold">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeFasilitas(idx)" class="hover:text-rose-700 cursor-pointer flex items-center" title="Hapus fasilitas">
                                        <x-icon name="close" class="w-3.5 h-3.5" />
                                    </button>
                                </span>
                            </template>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <span class="text-[10px] font-semibold text-stone-400 mr-1">Saran cepat:</span>
                            @foreach(['Pendamping', 'Konsumsi', 'Akomodasi Hotel', 'Asuransi', 'Tiket Pesawat', 'Perlengkapan Ibadah', 'Visa'] as $f)
                            <button type="button"
                                    x-show="!selectedFasilitas.some(s => s.toLowerCase() === '{{ strtolower($f) }}')"
                                    @click="addFasilitas('{{ $f }}')"
                                    class="px-2.5 py-1 rounded-full border border-stone-300 bg-white hover:bg-stone-100 text-stone-600 text-[10px] font-semibold inline-flex items-center gap-1 transition cursor-pointer">
                                <x-icon name="add" class="w-3 h-3" />
                                <span>{{ $f }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- gambar utama paket -->
                    <div class="space-y-2">
                        <label class="font-bold text-stone-800">Gambar Utama (JPG/PNG/WEBP, maks 5MB)</label>
                        <label class="block rounded-2xl border-2 border-dashed border-stone-300 bg-stone-50 hover:bg-stone-100/70 transition p-4 text-center cursor-pointer">
                            <input type="file" name="gambar_utama" x-ref="gambarInput" accept="image/png,image/jpeg,image/webp" @change="onFilePicked($event)" class="hidden">
                            <template x-if="previewImage">
                                <div class="space-y-2">
                                    <img :src="previewImage" alt="Preview Gambar Paket" class="mx-auto h-36 w-full max-w-sm rounded-xl object-cover shadow-xs">
                                    <p class="text-[11px] font-semibold text-[#063B25]">Klik untuk mengganti gambar</p>
                                </div>
                            </template>
                            <template x-if="!previewImage">
                                <div class="py-4 space-y-1 text-stone-500">
                                    <x-icon name="upload_file" class="w-7 h-7 mx-auto text-stone-400" />
                                    <p class="text-xs font-semibold">Pilih atau seret foto paket ke sini</p>
                                </div>
                            </template>
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="$refs.gambarInput.click()"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] inline-flex items-center gap-1.5 cursor-pointer">
                                <x-icon name="image" class="w-3.5 h-3.5" />
                                <span>Pilih Gambar</span>
                            </button>
                            <button type="button" @click="clearImage()" x-show="previewImage"
                                    class="px-4 py-2 rounded-xl bg-[#C51B1B] hover:bg-red-700 text-white font-bold text-[11px] inline-flex items-center gap-1.5 cursor-pointer">
                                <x-icon name="delete" class="w-3.5 h-3.5" />
                                <span>Hapus Gambar</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="font-bold text-stone-800 block">Status Paket</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition"
                                   :class="form.status === 'active' ? 'bg-[#CFE5D5] border-[#063B25] font-bold text-[#063B25]' : 'bg-white border-stone-300 text-stone-600'">
                                <span>Aktif</span>
                                <input type="radio" name="status" value="active" x-model="form.status" class="accent-[#063B25]">
                            </label>
                            <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition"
                                   :class="form.status === 'inactive' ? 'bg-rose-50 border-rose-500 font-bold text-rose-700' : 'bg-white border-stone-300 text-stone-600'">
                                <span>Non Aktif</span>
                                <input type="radio" name="status" value="inactive" x-model="form.status" class="accent-rose-600">
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3">
                        <button type="button" @click="goToStep2()" class="bg-[#063B25] text-white font-bold px-6 py-2.5 rounded-xl transition cursor-pointer">
                            Selanjutnya &rarr;
                        </button>
                    </div>
                </div>

                <!-- step 2: jadwal & maskapai -->
                <div x-show="modalStep === 2" class="space-y-4">
                    <h3 class="font-bold text-[#063B25] text-sm font-serif-display flex items-center gap-2">
                        <x-icon name="calendar_month" class="w-4 h-4" />
                        <span>Jadwal Keberangkatan &amp; Maskapai</span>
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="font-bold text-stone-800">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" x-model="form.tanggal_mulai" class="w-full px-4 py-2.5 rounded-xl border border-stone-300">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-stone-800">Tanggal Pulang</label>
                            <input type="date" name="tanggal_pulang" x-model="form.tanggal_pulang" class="w-full px-4 py-2.5 rounded-xl border border-stone-300">
                        </div>
                    </div>
                    <div x-show="hitungDurasi() !== null" class="flex items-center gap-2 rounded-xl bg-[#CFE5D5]/50 border border-[#063B25]/20 px-3.5 py-2 text-[11px] font-semibold text-[#063B25]">
                        <x-icon name="schedule" class="w-4 h-4" />
                        <span>Durasi perjalanan otomatis: <strong x-text="hitungDurasi() + ' Hari'"></strong> (dihitung dari Tanggal Mulai &amp; Pulang)</span>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-stone-800">Maskapai</label>
                        <select name="id_maskapai" x-model="form.id_maskapai" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white">
                            @foreach($maskapais as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_maskapai }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-stone-800">Kuota Jamaah (Kursi)</label>
                        <input type="number" name="kuota" x-model="form.kuota" min="1" class="w-full px-4 py-2.5 rounded-xl border border-stone-300">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="font-bold text-stone-800">Tempat Kumpul</label>
                            <input type="text" name="tempat_kumpul" x-model="form.tempat_kumpul" placeholder="Contoh: Terminal 3 Bandara Soekarno-Hatta" class="w-full px-4 py-2.5 rounded-xl border border-stone-300">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-stone-800">Tanggal &amp; Waktu Kumpul</label>
                            <input type="datetime-local" name="tanggal_kumpul" x-model="form.tanggal_kumpul" class="w-full px-4 py-2.5 rounded-xl border border-stone-300">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="modalStep = 1" class="border border-stone-300 text-stone-700 font-bold px-5 py-2.5 rounded-xl cursor-pointer">
                            &larr; Sebelumnya
                        </button>
                        <button type="button" @click="goToStep3()" class="bg-[#063B25] text-white font-bold px-6 py-2.5 rounded-xl transition cursor-pointer">
                            Selanjutnya &rarr;
                        </button>
                    </div>
                </div>

                <!-- step 3: hotel & tipe kamar -->
                <div x-show="modalStep === 3" class="space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-[#063B25] text-sm font-serif-display flex items-center gap-2">
                            <x-icon name="hotel" class="w-4 h-4" />
                            <span>Akomodasi Hotel &amp; Biaya Kamar</span>
                        </h3>
                        <a href="{{ route('supervisor.hotel.create') }}" target="_blank" class="text-[11px] font-bold text-[#063B25] hover:underline inline-flex items-center gap-1">
                            <x-icon name="add" class="w-3.5 h-3.5" />
                            <span>Tambah Hotel</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- hotel madinah -->
                        <div class="p-4 rounded-2xl border border-stone-200 bg-stone-50/70 space-y-2.5">
                            <div class="space-y-1.5">
                                <label class="font-bold text-stone-800 block">Hotel Madinah</label>
                                <select name="id_hotel_madinah" x-model="form.id_hotel_madinah" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white">
                                    @forelse($hotelsMadinah as $h)
                                        <option value="{{ $h->id }}">{{ $h->nama_hotel }} (★{{ $h->bintang_hotel ?? 5 }})</option>
                                    @empty
                                        <option value="">Belum ada hotel Madinah</option>
                                    @endforelse
                                </select>
                                <div class="h-28 rounded-2xl overflow-hidden border border-stone-200 mt-1">
                                    <img :src="getHotelImage(form.id_hotel_madinah, 'Madinah')" alt="Foto Hotel Madinah" class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>

                        <!-- hotel makkah -->
                        <div class="p-4 rounded-2xl border border-stone-200 bg-stone-50/70 space-y-2.5">
                            <div class="space-y-1.5">
                                <label class="font-bold text-stone-800 block">Hotel Makkah</label>
                                <select name="id_hotel_makkah" x-model="form.id_hotel_makkah" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white">
                                    @forelse($hotelsMakkah as $h)
                                        <option value="{{ $h->id }}">{{ $h->nama_hotel }} (★{{ $h->bintang_hotel ?? 5 }})</option>
                                    @empty
                                        <option value="">Belum ada hotel Makkah</option>
                                    @endforelse
                                </select>
                                <div class="h-28 rounded-2xl overflow-hidden border border-stone-200 mt-1">
                                    <img :src="getHotelImage(form.id_hotel_makkah, 'Makkah')" alt="Foto Hotel Makkah" class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- harga per tipe kamar -->
                    <div class="p-4 rounded-2xl border border-[#063B25]/20 bg-[#FBF9F5] space-y-3">
                        <div>
                            <h4 class="font-bold text-[#063B25] text-xs">Harga Paket All-In per Tipe Kamar (Sudah Termasuk Hotel Madinah &amp; Makkah)</h4>
                            <p class="text-[11px] text-stone-500 mt-0.5">Harga kamar tipe terendah (<strong>Quad</strong>) otomatis menjadi harga utama yang tampil di halaman katalog paket.</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="font-bold text-stone-800 text-[11px]">Harga Quad (4 Orang) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-stone-500">Rp</span>
                                    <input type="text" inputmode="numeric" :value="formatHargaInput(form.harga_quad)" @input="updateHargaField('harga_quad', $event)" required class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-stone-300 bg-white font-sans font-bold tabular-nums">
                                    <input type="hidden" name="harga_quad" :value="form.harga_quad">
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="font-bold text-stone-800 text-[11px]">Harga Triple (3 Orang) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-stone-500">Rp</span>
                                    <input type="text" inputmode="numeric" :value="formatHargaInput(form.harga_triple)" @input="updateHargaField('harga_triple', $event)" required class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-stone-300 bg-white font-sans font-bold tabular-nums">
                                    <input type="hidden" name="harga_triple" :value="form.harga_triple">
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="font-bold text-stone-800 text-[11px]">Harga Double (2 Orang) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-stone-500">Rp</span>
                                    <input type="text" inputmode="numeric" :value="formatHargaInput(form.harga_double)" @input="updateHargaField('harga_double', $event)" required class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-stone-300 bg-white font-sans font-bold tabular-nums">
                                    <input type="hidden" name="harga_double" :value="form.harga_double">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-stone-200">
                        <button type="button" @click="modalStep = 2" class="border border-stone-300 text-stone-700 font-bold px-5 py-2.5 rounded-xl cursor-pointer">
                            &larr; Sebelumnya
                        </button>
                        <button type="submit" class="bg-[#063B25] hover:bg-emerald-900 text-white font-bold px-7 py-2.5 rounded-xl shadow-md transition cursor-pointer inline-flex items-center gap-1.5">
                            <x-icon name="save" class="w-4 h-4" />
                            <span x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Simpan'">Simpan</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

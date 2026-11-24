@extends('layouts.admin')

@section('title', 'Administrasi Hotel & Pesawat - Admin ROZWIA')

@section('content')
@php
    $jadwalsData = $jadwals->map(function ($jdw) use ($hotels) {
        $paketHotels = $jdw->paket?->paketTipeKamars
            ?->map(fn ($ptk) => $ptk->hotel)
            ->filter()
            ->unique('id')
            ->values();

        $hotelList = ($paketHotels && $paketHotels->isNotEmpty()) ? $paketHotels : $hotels;
        $hotelNames = $hotelList->pluck('nama_hotel')->take(2)->implode(' & ') ?: 'Belum diatur';
        $maxBintang = $hotelList->max('bintang') ?? 5;

        return [
            'id'                    => $jdw->id,
            'paket'                 => $jdw->paket?->nama_paket ?? 'Paket Umroh',
            'tanggal_label'         => $jdw->tanggal_keberangkatan?->format('d/m/Y') ?? '-',
            'tanggal_input'         => $jdw->tanggal_keberangkatan?->format('Y-m-d') ?? '',
            'kuota'                 => $jdw->kuota ?? 0,
            'id_maskapai'           => $jdw->id_maskapai,
            'nama_maskapai'         => $jdw->maskapai?->nama_maskapai ?? 'Belum diatur',
            'nomor_penerbangan'     => $jdw->nomor_penerbangan ?? '-',
            'hotel_names'           => $hotelNames,
            'bintang'               => $maxBintang,
        ];
    })->values();

    $jamaahData = $jamaahDetails->map(function ($j) use ($hotels) {
        $jdw = $j->booking?->jadwal;
        $paketHotels = $jdw?->paket?->paketTipeKamars
            ?->map(fn ($ptk) => $ptk->hotel)
            ->filter()
            ->unique('id')
            ->values();
        $firstHotel = ($paketHotels && $paketHotels->isNotEmpty()) ? $paketHotels->first() : $hotels->first();

        $maskapaiReady = !empty($j->seat_number) || ($j->tracking_status && $j->tracking_status !== 'Persiapan');
        $hotelReady    = !empty($j->manifest_room);

        return [
            'id'             => $j->id,
            'jadwal_id'      => $jdw?->id,
            'nama'           => $j->nama_lengkap,
            'akun'           => $j->booking?->user?->name ?? $j->nama_lengkap,
            'paket'          => $jdw?->paket?->nama_paket ?? 'Paket Umroh',
            'nama_maskapai'  => $jdw?->maskapai?->nama_maskapai ?? 'Saudia Airlines',
            'nama_hotel'     => $firstHotel?->nama_hotel ?? 'Hotel Makkah & Madinah',
            'bintang'        => $firstHotel?->bintang ?? 5,
            'kamar'          => $j->manifest_room ?? '',
            'bus'            => $j->manifest_bus ?? '',
            'kursi'          => $j->seat_number ?? '',
            'tracking'       => $j->tracking_status ?? 'Persiapan',
            'maskapai_ready' => $maskapaiReady,
            'hotel_ready'    => $hotelReady,
        ];
    })->values();
@endphp

<div x-data="{
        jadwals: {{ Js::from($jadwalsData) }},
        selectedJadwalId: {{ $jadwalsData->first()['id'] ?? 'null' }},
        jamaahList: {{ Js::from($jamaahData) }},
        searchQuery: '',
        filterByJadwal: false,
        flightModalOpen: false,
        hotelModalOpen: false,
        jamaahModalOpen: false,
        selectedJamaah: { id: null, nama: '', paket: '', kamar: '', bus: '', kursi: '', tracking: 'Persiapan' },
        get activeJadwal() {
            return this.jadwals.find(j => j.id == this.selectedJadwalId) || this.jadwals[0] || {
                id: null, paket: 'Belum Ada Jadwal', tanggal_label: '-', tanggal_input: '',
                kuota: 0, id_maskapai: '', nama_maskapai: '-', nomor_penerbangan: '-',
                hotel_names: '-', bintang: 5
            };
        },
        filteredJamaah() {
            const q = this.searchQuery.toLowerCase();
            return this.jamaahList.filter(j => {
                const matchSearch = q === '' ||
                    j.nama.toLowerCase().includes(q) ||
                    j.akun.toLowerCase().includes(q) ||
                    j.paket.toLowerCase().includes(q);
                const matchJadwal = !this.filterByJadwal || !this.selectedJadwalId || j.jadwal_id == this.selectedJadwalId;
                return matchSearch && matchJadwal;
            });
        },
        openJamaahModal(j) {
            this.selectedJamaah = Object.assign({}, j);
            this.jamaahModalOpen = true;
        }
     }"
     class="space-y-5 font-serif">

    <!-- header halaman -->
    <div class="bg-white rounded-2xl px-6 py-5 shadow-xs border border-stone-200/80">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">Administrasi Hotel dan Pesawat</h1>
        <p class="text-xs sm:text-sm text-stone-600 mt-1">
            Kelola penerbangan, hotel, serta pembagian kamar dan bus untuk setiap keberangkatan.
        </p>
    </div>

    <!-- banner info -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
        <div class="lg:col-span-5 bg-[#DFC99B] rounded-2xl px-5 py-3.5 flex items-center justify-between gap-3 shadow-xs">
            <select x-model="selectedJadwalId"
                    class="w-full bg-transparent text-stone-900 font-bold text-sm focus:outline-none cursor-pointer">
                @forelse($jadwals as $jdw)
                    <option value="{{ $jdw->id }}">
                        {{ $jdw->paket?->nama_paket ?? 'Paket' }} • {{ $jdw->tanggal_keberangkatan?->format('d/m/Y') }}
                    </option>
                @empty
                    <option value="">Belum ada jadwal tersedia</option>
                @endforelse
            </select>
        </div>

        <div class="lg:col-span-7 bg-[#DFC99B] rounded-2xl px-6 py-3.5 flex items-center justify-between gap-4 shadow-xs text-stone-900 font-bold text-sm">
            <span>Jadwal : <span x-text="activeJadwal.id ? 'Tersedia' : 'Kosong'"></span></span>
            <span>{{ $jamaahDetails->count() }} Jamaah Terdaftar</span>
        </div>
    </div>

    <!-- kartu ringkasan maskapai & hotel -->
    <div class="bg-white rounded-[24px] p-5 sm:p-6 shadow-xs border border-stone-200/80 space-y-5 font-sans">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-stone-100">
            <div class="space-y-1">
                <h2 class="text-sm sm:text-base font-bold text-[#0B3B24] font-serif">
                    Verifikasi Kesiapan Tiket Maskapai &amp; Reservasi Hotel Jamaah
                </h2>
                <p class="text-[11px] text-stone-500">
                    Kelola status penerbangan tiket dan reservasi hotel per jamaah aktif. Klik ubah data untuk memperbarui jadwal maskapai atau lihat daftar hotel.
                </p>
            </div>
            <div class="shrink-0 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">PILIH PAKET &amp; TANGGAL</span>
                <select x-model="selectedJadwalId"
                        class="px-3.5 py-2 rounded-xl border border-stone-300 bg-white text-xs font-semibold text-stone-800 focus:outline-none focus:ring-1 focus:ring-[#0B3B24]">
                    @forelse($jadwals as $jdw)
                        <option value="{{ $jdw->id }}">
                            {{ $jdw->paket?->nama_paket ?? 'Paket' }} • {{ $jdw->tanggal_keberangkatan?->format('d/m/Y') }}
                        </option>
                    @empty
                        <option value="">Belum ada jadwal</option>
                    @endforelse
                </select>
            </div>
        </div>

        <!-- 2 Kartu Side-by-Side: Maskapai Penerbangan Kloter & Akomodasi Hotel Kloter -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Kiri: Maskapai Penerbangan Kloter -->
            <div class="p-4 sm:p-5 rounded-2xl border border-stone-200 bg-[#FCFBF8] flex flex-col justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">MASKAPAI PENERBANGAN KLOTER</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#E6F4EA] text-[#137333]">Terjadwal</span>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 font-serif"
                        x-text="activeJadwal.nama_maskapai"></h3>
                    <p class="text-[11px] text-stone-500">
                        Kuota Keberangkatan: <strong class="text-stone-700" x-text="activeJadwal.kuota + ' Jamaah'"></strong>
                    </p>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-stone-200/70 text-[11px]">
                    <span class="text-stone-600">Keberangkatan: <strong class="text-stone-900" x-text="activeJadwal.tanggal_label"></strong></span>
                    <button type="button"
                            x-show="activeJadwal.id"
                            @click="flightModalOpen = true"
                            class="text-[#0B3B24] font-bold hover:underline cursor-pointer">
                        Ubah Data Penerbangan
                    </button>
                </div>
            </div>

            <!-- Kanan: Akomodasi Hotel Kloter -->
            <div class="p-4 sm:p-5 rounded-2xl border border-stone-200 bg-[#FCFBF8] flex flex-col justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">AKOMODASI HOTEL KLOTER</span>
                        <span class="text-[11px] font-bold text-amber-600">
                            Bintang <span class="text-amber-500">★★★★★</span>
                        </span>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 font-serif" x-text="activeJadwal.hotel_names"></h3>
                    <p class="text-[11px] text-stone-500">Kapasitas Booking: Quad, Triple, &amp; Double Room</p>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-stone-200/70 text-[11px]">
                    <span class="text-stone-600">Total Hotel Terdaftar: <strong class="text-stone-900">{{ $hotels->count() }} Hotel</strong></span>
                    <button type="button"
                            @click="hotelModalOpen = true"
                            class="text-[#0B3B24] font-bold hover:underline cursor-pointer">
                        Lihat Daftar Hotel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- tabel data jamaah & kesiapan -->
    <div class="bg-white rounded-[24px] p-5 sm:p-6 shadow-xs border border-stone-200/80 space-y-4 font-sans">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h2 class="text-sm sm:text-base font-bold text-[#0B3B24] font-serif flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-xs">✓</span>
                    Data Jamaah Aktif &amp; Status Kesiapan
                </h2>
                <p class="text-[11px] text-stone-500">
                    Klik tombol aksi untuk mengatur kamar hotel, bus rombongan, nomor kursi pesawat, dan status perjalanan jamaah.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative w-full sm:w-56">
                    <span class="absolute left-3 top-2 text-stone-400 text-xs">🔍</span>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari nama jamaah..."
                           class="w-full pl-8 pr-3 py-1.5 rounded-full border border-stone-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#0B3B24]">
                </div>

                <button type="button"
                        @click="filterByJadwal = !filterByJadwal"
                        :class="filterByJadwal ? 'bg-[#0B3B24] text-white border-[#0B3B24]' : 'bg-[#E6F4EA] text-[#137333] border-[#A8DAB5]'"
                        class="px-3.5 py-1.5 rounded-xl border text-[11px] font-bold transition cursor-pointer">
                    <span x-text="filterByJadwal ? 'Tampilkan Semua' : 'Filter Kloter Ini'"></span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-stone-200 text-[10px] font-bold uppercase tracking-wider text-stone-500 bg-stone-50/70">
                        <th class="py-3 px-3 rounded-tl-xl">NAMA JAMAAH</th>
                        <th class="py-3 px-3">KAMAR &amp; BUS</th>
                        <th class="py-3 px-3">NAMA MASKAPAI</th>
                        <th class="py-3 px-3">NAMA HOTEL</th>
                        <th class="py-3 px-3">BINTANG HOTEL</th>
                        <th class="py-3 px-3 text-center">STATUS MASKAPAI</th>
                        <th class="py-3 px-3 text-center">STATUS HOTEL</th>
                        <th class="py-3 px-3 rounded-tr-xl text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <template x-for="j in filteredJamaah()" :key="j.id">
                        <tr class="hover:bg-stone-50/80 transition">
                            <td class="py-3.5 px-3">
                                <strong class="text-stone-900 block font-serif text-xs" x-text="j.nama"></strong>
                                <span class="text-[10px] text-stone-500" x-text="j.paket"></span>
                            </td>
                            <td class="py-3.5 px-3 text-[11px] text-stone-700">
                                <div>Kamar: <strong class="text-stone-900" x-text="j.kamar || '-'"></strong></div>
                                <div class="text-stone-500">Bus: <span x-text="j.bus || '-'"></span> • Kursi: <span x-text="j.kursi || '-'"></span></div>
                            </td>
                            <td class="py-3.5 px-3 text-stone-700 font-medium" x-text="j.nama_maskapai"></td>
                            <td class="py-3.5 px-3 text-stone-700 font-medium" x-text="j.nama_hotel"></td>
                            <td class="py-3.5 px-3">
                                <span class="text-[11px] font-bold text-stone-800 block" x-text="'Bintang ' + j.bintang"></span>
                                <span class="text-amber-500 text-[11px] tracking-tight">★★★★★</span>
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                <span :class="j.maskapai_ready ? 'bg-[#E6F4EA] text-[#137333] border-[#A8DAB5]' : 'bg-[#FEF7E0] text-[#B06000] border-[#FCE293]'"
                                      class="inline-block px-3 py-1 rounded-full border text-[10px] font-bold">
                                    <span x-text="j.maskapai_ready ? '✓ Ready' : 'Not Ready'"></span>
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                <span :class="j.hotel_ready ? 'bg-[#E6F4EA] text-[#137333] border-[#A8DAB5]' : 'bg-[#FEF7E0] text-[#B06000] border-[#FCE293]'"
                                      class="inline-block px-3 py-1 rounded-full border text-[10px] font-bold">
                                    <span x-text="j.hotel_ready ? '✓ Ready' : 'Not Ready'"></span>
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                <button type="button"
                                        @click="openJamaahModal(j)"
                                        :class="(j.maskapai_ready && j.hotel_ready)
                                            ? 'bg-white text-stone-700 border border-stone-300 hover:bg-stone-100'
                                            : 'bg-[#0B3B24] text-white hover:bg-emerald-900'"
                                        class="px-3.5 py-1.5 rounded-xl text-[11px] font-bold transition cursor-pointer shadow-2xs">
                                    <span x-text="(j.maskapai_ready && j.hotel_ready) ? 'Sudah Lengkap' : 'Atur & Verifikasi'"></span>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredJamaah().length === 0">
                        <td colspan="8" class="py-8 text-center text-stone-400 italic">
                            Belum ada data jamaah yang sesuai.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: UBAH DATA PENERBANGAN KLOTER -->
    <div x-show="flightModalOpen"
         x-transition.opacity
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs font-sans">
        <div @click.away="flightModalOpen = false" class="bg-white rounded-[28px] p-6 max-w-md w-full shadow-2xl border border-stone-200 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="text-lg font-bold text-[#0B3B24] font-serif">Ubah Data Penerbangan Kloter</h3>
                <button type="button" @click="flightModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold cursor-pointer">✕</button>
            </div>

            <form :action="'{{ url('/admin/hotel/jadwal') }}/' + activeJadwal.id + '/update'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="p-3 rounded-xl bg-stone-50 border border-stone-200">
                    <span class="text-stone-500 block">Paket:</span>
                    <strong class="text-sm text-[#0B3B24]" x-text="activeJadwal.paket"></strong>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-stone-700">Maskapai Penerbangan</label>
                    <select name="id_maskapai" x-model="activeJadwal.id_maskapai" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white">
                        @foreach($maskapais as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_maskapai }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-stone-700">Tanggal Keberangkatan</label>
                    <input type="date" name="tanggal_keberangkatan" x-model="activeJadwal.tanggal_input" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="flightModalOpen = false" class="px-4 py-2 rounded-xl border border-stone-300 text-stone-700 font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold cursor-pointer">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: DAFTAR HOTEL TERDAFTAR -->
    <div x-show="hotelModalOpen"
         x-transition.opacity
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs font-sans">
        <div @click.away="hotelModalOpen = false" class="bg-white rounded-[28px] p-6 max-w-lg w-full shadow-2xl border border-stone-200 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="text-lg font-bold text-[#0B3B24] font-serif">Daftar Hotel Akomodasi</h3>
                <button type="button" @click="hotelModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold cursor-pointer">✕</button>
            </div>
            <div class="space-y-2.5 max-h-80 overflow-y-auto text-xs">
                @forelse($hotels as $h)
                <div class="p-3.5 rounded-2xl bg-[#FBF9F5] border border-stone-200 flex items-center justify-between gap-3">
                    <div>
                        <strong class="text-sm text-[#0B3B24] block font-serif">{{ $h->nama_hotel }}</strong>
                        <span class="text-stone-500">{{ $h->lokasi }}</span>
                    </div>
                    <span class="bg-amber-100 text-amber-900 px-3 py-1 rounded-full font-bold text-[11px] shrink-0">
                        ★ Bintang {{ $h->bintang ?? 5 }}
                    </span>
                </div>
                @empty
                <p class="text-stone-500 italic text-center py-4">Belum ada data hotel.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- MODAL 3: ATUR ALOKASI & KESIAPAN JAMAAH -->
    <div x-show="jamaahModalOpen"
         x-transition.opacity
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs font-sans">
        <div @click.away="jamaahModalOpen = false" class="bg-white rounded-[28px] p-6 max-w-md w-full shadow-2xl border border-stone-200 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="text-lg font-bold text-[#0B3B24] font-serif">Atur Alokasi &amp; Kesiapan Jamaah</h3>
                <button type="button" @click="jamaahModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold cursor-pointer">✕</button>
            </div>

            <form x-show="selectedJamaah.id" :action="'{{ url('/admin/hotel/jamaah') }}/' + selectedJamaah.id + '/update'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="p-3 rounded-2xl bg-stone-50 border border-stone-200">
                    <span class="text-stone-500 block">Jamaah Terpilih:</span>
                    <strong class="text-sm text-[#0B3B24]" x-text="selectedJamaah.nama"></strong>
                    <span class="text-[11px] text-stone-500 block" x-text="selectedJamaah.paket"></span>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-stone-700">Nomor Kamar Hotel (mengaktifkan Status Hotel Ready)</label>
                    <input type="text" name="manifest_room" x-model="selectedJamaah.kamar" placeholder="Contoh: Kamar 301" class="w-full px-3.5 py-2 rounded-xl border border-stone-300">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="font-bold text-stone-700">Bus Rombongan</label>
                        <input type="text" name="manifest_bus" x-model="selectedJamaah.bus" placeholder="Bus 1" class="w-full px-3.5 py-2 rounded-xl border border-stone-300">
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-stone-700">No. Kursi Pesawat (Ready)</label>
                        <input type="text" name="seat_number" x-model="selectedJamaah.kursi" placeholder="14A" class="w-full px-3.5 py-2 rounded-xl border border-stone-300">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-stone-700">Status Perjalanan (Live Tracking)</label>
                    <select name="tracking_status" x-model="selectedJamaah.tracking" class="w-full px-3.5 py-2 rounded-xl border border-stone-300 bg-white">
                        @foreach(['Persiapan', 'Siap_Berangkat', 'Dalam_Penerbangan', 'Tiba_di_Arab_Saudi', 'Pelaksanaan_Ibadah', 'Perjalanan_Pulang', 'Tiba_di_Indonesia', 'Selesai'] as $st)
                            <option value="{{ $st }}">{{ str_replace('_', ' ', $st) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="jamaahModalOpen = false" class="px-4 py-2 rounded-xl border border-stone-300 text-stone-700 font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold px-5 py-2 rounded-xl shadow-md transition cursor-pointer">
                        💾 Simpan Alokasi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

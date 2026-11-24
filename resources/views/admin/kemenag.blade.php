@extends('layouts.admin')

@section('title', 'Status Surat Kemenag Dan Visa - Admin ROZWIA')

@section('content')
@php
    $jamaahJson = $jamaahDetails->map(function ($j) {
        $isLunas = $j->booking ? \App\Helpers\RozwiaHelper::isBookingLunas($j->booking) : false;
        return [
            'id'      => $j->id,
            'nama'    => $j->nama_lengkap,
            'paket'   => $j->booking?->jadwal?->paket?->nama_paket ?? 'Paket Umroh',
            'isLunas' => $isLunas,
            'kemenag' => \App\Models\JamaahDetail::normalizeStatus($j->status_kemenag),
            'visa'    => \App\Models\JamaahDetail::normalizeStatus($j->status_visa),
            'noVisa'  => $j->nomor_visa ?? '',
            'tglVisa' => $j->tanggal_terbit_visa ? $j->tanggal_terbit_visa->format('Y-m-d') : '',
        ];
    })->values();
@endphp

<div x-data="{
        jamaahList: {{ Js::from($jamaahJson) }},
        selectedId: {{ $jamaahJson->first()['id'] ?? 'null' }},
        selectedJamaah: '{{ addslashes($jamaahJson->first()['nama'] ?? '-') }}',
        selectedIsLunas: {{ ($jamaahJson->first()['isLunas'] ?? false) ? 'true' : 'false' }},
        statusKemenag: '{{ $jamaahJson->first()['kemenag'] ?? 'Belum Diajukan' }}',
        statusVisa: '{{ $jamaahJson->first()['visa'] ?? 'Belum Diajukan' }}',
        nomorVisa: '{{ addslashes($jamaahJson->first()['noVisa'] ?? '') }}',
        tanggalTerbit: '{{ $jamaahJson->first()['tglVisa'] ?? '' }}',
        filterState: 'semua',
        highlightBox: false,
        pilihJamaah(item) {
            this.selectedId = item.id;
            this.selectedJamaah = item.nama;
            this.selectedIsLunas = !!item.isLunas;
            this.statusKemenag = item.kemenag;
            this.statusVisa = item.visa;
            this.nomorVisa = item.visa === 'Terbit' ? item.noVisa : '';
            this.tanggalTerbit = item.visa === 'Terbit' ? item.tglVisa : '';
            this.highlightBox = true;
            setTimeout(() => { this.highlightBox = false; }, 1200);
            this.$nextTick(() => {
                const el = document.getElementById('boxPerbaharuiStatus');
                if (el && window.innerWidth < 1024) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        },
        setStatusVisa(opt) {
            this.statusVisa = opt;
            if (opt !== 'Terbit') {
                this.nomorVisa = '';
                this.tanggalTerbit = '';
            }
        },
        filteredList() {
            return this.jamaahList.filter(j => {
                if (this.filterState === 'perlu_tindakan') return j.kemenag !== 'Terbit' || j.visa !== 'Terbit';
                if (this.filterState === 'sudah_terbit') return j.kemenag === 'Terbit' && j.visa === 'Terbit';
                return true;
            });
        }
     }" 
     class="space-y-6">
    
    <!-- Header -->
    <div class="space-y-1">
        <h1 class="text-3xl sm:text-4xl font-bold font-serif text-stone-900 tracking-tight">Status Surat Kemenag Dan Visa</h1>
        <p class="text-xs sm:text-sm text-stone-600 font-serif">
            Memantau Proses Surat Kemenag dan Visa setiap jamaah, lalu perbaharui statusnya secara permanen.
        </p>
    </div>

    <!-- 3 Kartu Statistik Putih (Perlu tindakan paling kiri, lanjut Kemenag dan Visa) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-3xl font-serif">
        <div class="bg-white p-5 rounded-2xl border border-stone-200/80 shadow-xs space-y-2">
            <span class="text-xs text-rose-600 font-medium block">Perlu tindakan</span>
            <p class="text-2xl font-bold text-stone-900">{{ $perluTindakan }} Jamaah</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200/80 shadow-xs space-y-2">
            <span class="text-xs text-stone-700 font-medium block">Surat Kemenag Terbit</span>
            <p class="text-2xl font-bold text-stone-900">{{ $kemenagTerbit }} Jamaah</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200/80 shadow-xs space-y-2">
            <span class="text-xs text-stone-700 font-medium block">Visa Terbit</span>
            <p class="text-2xl font-bold text-stone-900">{{ $visaTerbit }} Jamaah</p>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="flex flex-wrap items-center gap-3 pt-2 font-serif">
        <button type="button" 
                @click="filterState = 'semua'" 
                :class="filterState === 'semua' ? 'bg-[#0B3B24] text-white' : 'bg-white text-stone-800 border border-stone-800'"
                class="px-6 py-2 rounded-full text-xs font-bold transition shadow-xs cursor-pointer">
            Semua ({{ $jamaahDetails->count() }})
        </button>

        <button type="button" 
                @click="filterState = 'perlu_tindakan'" 
                :class="filterState === 'perlu_tindakan' ? 'bg-[#0B3B24] text-white border-[#0B3B24]' : 'bg-white text-rose-700 border border-stone-800'"
                class="px-6 py-2 rounded-full text-xs font-medium transition shadow-xs cursor-pointer">
            Perlu tindakan ({{ $perluTindakan }})
        </button>

        <button type="button" 
                @click="filterState = 'sudah_terbit'" 
                :class="filterState === 'sudah_terbit' ? 'bg-[#0B3B24] text-white border-[#0B3B24]' : 'bg-white text-stone-800 border border-stone-800'"
                class="px-6 py-2 rounded-full text-xs font-medium transition shadow-xs cursor-pointer">
            Sudah Terbit
        </button>
    </div>

    <!-- Konten Utama: Tabel Kiri & Form Perbaharui Status Kanan -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- tabel status jamaah -->
        <div class="lg:col-span-8 bg-white rounded-[24px] p-6 shadow-sm border border-stone-200/80 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[11px] font-bold uppercase tracking-wider text-amber-800 font-serif border-b border-stone-100">
                        <th class="pb-4 pr-3">JAMAAH</th>
                        <th class="pb-4 px-3 text-center">SURAT KEMENAG</th>
                        <th class="pb-4 px-3 text-center">VISA</th>
                        <th class="pb-4 pl-3 text-center">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-xs font-serif">
                    <template x-for="item in filteredList()" :key="item.id">
                        <tr class="hover:bg-stone-50/70 transition" :class="selectedId === item.id ? 'bg-amber-50/70 ring-1 ring-amber-300' : ''">
                            <td class="py-4 pr-3">
                                <span class="font-bold text-stone-900 block" x-text="item.nama"></span>
                                <span class="text-[10px] text-stone-500 block" x-text="item.paket"></span>
                                <span x-show="!item.isLunas" class="inline-flex items-center gap-1 mt-1 rounded-full bg-rose-50 border border-rose-200 px-2 py-0.5 text-[9px] font-sans font-bold text-rose-700">
                                    <x-icon name="lock" class="w-3 h-3" /> Belum Lunas
                                </span>
                                <span x-show="item.isLunas" class="inline-flex items-center gap-1 mt-1 rounded-full bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[9px] font-sans font-bold text-emerald-700">
                                    <x-icon name="check_plain" class="w-3 h-3" /> Lunas
                                </span>
                            </td>
                            <td class="py-4 px-3 text-center">
                                <span :class="{
                                    'bg-[#E8EFEF] text-[#2B4E4E] border-[#C1D2D2]': item.kemenag === 'Belum Diajukan',
                                    'bg-yellow-100 text-yellow-800 border-yellow-300': item.kemenag === 'Diproses',
                                    'bg-emerald-600 text-white border-emerald-700 font-bold': item.kemenag === 'Terbit',
                                    'bg-[#F48C8C] text-stone-900 border-[#DC6D6D]': item.kemenag === 'Ditolak'
                                }" class="inline-block px-3 py-1 rounded-full text-[11px] border font-medium" x-text="item.kemenag"></span>
                            </td>
                            <td class="py-4 px-3 text-center">
                                <span :class="{
                                    'bg-[#E8EFEF] text-[#2B4E4E] border-[#C1D2D2]': item.visa === 'Belum Diajukan',
                                    'bg-yellow-100 text-yellow-800 border-yellow-300': item.visa === 'Diproses',
                                    'bg-emerald-600 text-white border-emerald-700 font-bold': item.visa === 'Terbit',
                                    'bg-[#F48C8C] text-stone-900 border-[#DC6D6D]': item.visa === 'Ditolak'
                                }" class="inline-block px-3 py-1 rounded-full text-[11px] border font-medium" x-text="item.visa"></span>
                            </td>
                            <td class="py-4 pl-3 text-center">
                                <button type="button" 
                                        @click="pilihJamaah(item)" 
                                        :class="selectedId === item.id ? 'bg-[#0B3B24] text-white border-[#0B3B24] font-bold shadow-md' : 'bg-white hover:bg-stone-100 text-stone-800 border border-stone-400'"
                                        class="px-4 py-1.5 rounded-full text-[11px] font-medium transition cursor-pointer shadow-2xs inline-flex items-center gap-1">
                                    <span x-show="selectedId === item.id" class="inline-flex"><x-icon name="edit" class="w-3.5 h-3.5" /></span>
                                    <span>Ubah Status</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div x-show="filteredList().length === 0" class="py-8 text-center text-xs text-stone-500 italic">
                Belum ada data jamaah pada filter ini.
            </div>
        </div>

        <!-- form update status -->
        <div id="boxPerbaharuiStatus"
             :class="highlightBox ? 'ring-4 ring-[#D8A941] shadow-2xl scale-[1.02] bg-[#FCFAF6] border-[#0B3B24]' : 'border-stone-200/80'"
             class="lg:col-span-4 bg-white rounded-[24px] p-6 shadow-sm border space-y-5 font-serif transition-all duration-300 ease-out">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl font-bold text-stone-900">Perbaharui Status</h2>
                    <span x-show="highlightBox" 
                          x-transition:enter="transition ease-out duration-200"
                          x-transition:enter-start="opacity-0 scale-75"
                          x-transition:enter-end="opacity-100 scale-100"
                          class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-bold animate-pulse">
                        Pilihan Aktif
                    </span>
                </div>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse" title="Form Siap"></span>
            </div>

            <form x-show="selectedId"
                  x-transition:enter="transition ease-out duration-300"
                  x-transition:enter-start="opacity-0 translate-y-2"
                  x-transition:enter-end="opacity-100 translate-y-0"
                  :action="'{{ url('/admin/kemenag') }}/' + selectedId + '/update'" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="status_kemenag" :value="statusKemenag">
                <input type="hidden" name="status_visa" :value="statusVisa">

                <div class="text-xs">
                    <span class="text-stone-600">Jamaah : </span>
                    <strong class="text-stone-900 text-sm" x-text="selectedJamaah"></strong>
                </div>

                <div x-show="!selectedIsLunas" class="rounded-2xl border border-rose-200 bg-rose-50 p-3.5 text-[11px] font-sans text-rose-800 leading-relaxed flex items-start gap-2">
                    <x-icon name="lock" class="w-4 h-4 shrink-0 text-rose-600 mt-0.5" />
                    <div><strong>Belum Lunas:</strong> Jamaah ini belum menyelesaikan pelunasan di menu <strong>Kelola Pembayaran</strong>. Status Surat Kemenag &amp; Visa baru bisa diubah setelah pembayaran berstatus <strong>Lunas</strong>.</div>
                </div>

                <!-- Pilihan Status Surat Kemenag -->
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-stone-700">Surat Kemenag</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['Belum Diajukan', 'Diproses', 'Ditolak', 'Terbit'] as $opt)
                        <button type="button"
                                @click="statusKemenag = '{{ $opt }}'"
                                :disabled="!selectedIsLunas"
                                :class="statusKemenag === '{{ $opt }}' ? ('{{ $opt }}' === 'Terbit' ? 'bg-emerald-600 text-white font-bold border-emerald-700' : ('{{ $opt }}' === 'Ditolak' ? 'bg-rose-600 text-white font-bold border-rose-700' : 'bg-[#0B3B24] text-white font-bold border-[#0B3B24]')) : 'bg-white text-stone-700 border border-stone-400'"
                                class="py-1.5 px-3 rounded-full text-[11px] border transition cursor-pointer text-center disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ $opt }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Pilihan Status Visa -->
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-stone-700">Visa</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['Belum Diajukan', 'Diproses', 'Ditolak', 'Terbit'] as $opt)
                        <button type="button"
                                @click="setStatusVisa('{{ $opt }}')"
                                :disabled="!selectedIsLunas"
                                :class="statusVisa === '{{ $opt }}' ? ('{{ $opt }}' === 'Terbit' ? 'bg-emerald-600 text-white font-bold border-emerald-700' : ('{{ $opt }}' === 'Ditolak' ? 'bg-rose-600 text-white font-bold border-rose-700' : 'bg-[#0B3B24] text-white font-bold border-[#0B3B24]')) : 'bg-white text-stone-700 border border-stone-400'"
                                class="py-1.5 px-3 rounded-full text-[11px] border transition cursor-pointer text-center disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ $opt }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- input nomor & tanggal terbit visa -->
                <div class="space-y-2.5 pt-1" x-show="statusVisa === 'Terbit'" x-transition>
                    <label class="block text-xs font-medium text-emerald-800 font-bold">Nomor Visa &amp; Tanggal Terbit</label>
                    <input type="text"
                           name="nomor_visa"
                           x-model="nomorVisa"
                           :disabled="statusVisa !== 'Terbit' || !selectedIsLunas"
                           placeholder="Nomor Visa (misal: V-987214)"
                           class="w-full bg-[#E6D6BC]/80 text-stone-900 placeholder-stone-600 text-xs font-sans tabular-nums px-4 py-2.5 rounded-full focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                    <input type="date"
                           name="tanggal_terbit_visa"
                           x-model="tanggalTerbit"
                           :disabled="statusVisa !== 'Terbit' || !selectedIsLunas"
                           class="w-full bg-[#E6D6BC]/80 text-stone-900 text-xs font-sans tabular-nums px-4 py-2.5 rounded-full focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                </div>

                <div x-show="statusVisa !== 'Terbit'" class="rounded-xl bg-stone-100 px-3.5 py-2.5 text-[11px] font-sans text-stone-600 flex items-center gap-2">
                    <x-icon name="info" class="w-4 h-4 shrink-0 text-stone-500" />
                    <span>Nomor Visa &amp; Tanggal Terbit baru dapat diisi apabila status Visa diubah menjadi <strong>Terbit</strong>.</span>
                </div>

                <div class="pt-2 flex justify-center">
                    <button type="submit"
                            :disabled="!selectedIsLunas"
                            :class="!selectedIsLunas ? 'bg-stone-300 text-stone-500 cursor-not-allowed' : 'bg-[#0B3B24] hover:bg-emerald-900 text-white cursor-pointer'"
                            class="font-bold text-xs px-8 py-2.5 rounded-full shadow-md transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection

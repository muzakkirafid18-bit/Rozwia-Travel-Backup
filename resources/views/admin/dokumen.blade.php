@extends('layouts.admin')

@section('title', 'Verifikasi Dokumen Pribadi - Admin ROZWIA')

@section('content')
<div x-data="{
        filterStatus: 'semua',
        searchQuery: '',
        modalOpen: false,
        selectedJamaah: null,
        selectedDoc: null,
        adminNotes: '',
        actionStatus: 'Diverifikasi',
        jamaahList: [
            @foreach($jamaahWithDokumens as $j)
            @php
                $docs = $j->dokumens;
                $verifiedCount = $docs->where('status', 'Diverifikasi')->count();
                $totalDocs = $docs->count();
                if ($docs->contains('status', 'Ditolak')) {
                    $overall = 'Ditolak';
                } elseif ($docs->contains('status', 'Perlu_Diperbaiki')) {
                    $overall = 'Perlu Diperbaiki';
                } elseif ($totalDocs > 0 && $verifiedCount === $totalDocs) {
                    $overall = 'Diverifikasi';
                } else {
                    $overall = 'Menunggu Verifikasi';
                }
                $latestCreated = $docs->sortByDesc('created_at')->first()?->created_at;
            @endphp
            {
                id: {{ $j->id }},
                nama: '{{ addslashes($j->nama_lengkap ?? $j->booking?->user?->name ?? 'Jamaah') }}',
                paket: '{{ addslashes($j->booking?->jadwal?->paket?->nama_paket ?? '-') }}',
                kode_booking: '{{ addslashes($j->booking?->kode_booking ?? '-') }}',
                nik: '{{ addslashes($j->nik ?: '-') }}',
                passport_number: '{{ addslashes($j->passport_number ?: '-') }}',
                jenis_kelamin: '{{ addslashes($j->jenis_kelamin ?: '-') }}',
                no_telepon: '{{ addslashes($j->nomor_telepon ?: $j->booking?->user?->nomor_telepon ?: '-') }}',
                email: '{{ addslashes($j->email ?: '-') }}',
                diunggah: '{{ $latestCreated ? $latestCreated->format('d/m/Y') : '-' }}',
                status: '{{ $overall }}',
                totalDocs: {{ $totalDocs }},
                verifiedCount: {{ $verifiedCount }},
                dokumens: [
                    @foreach($docs as $doc)
                    @php
                        $docSizeLabel = '-';
                        if ($doc->file_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($doc->file_path)) {
                            $docBytes = \Illuminate\Support\Facades\Storage::disk('private')->size($doc->file_path);
                            $docSizeLabel = number_format($docBytes / 1048576, 2) . ' MB (' . round($docBytes / 1024) . ' KB)';
                        }
                    @endphp
                    {
                        id: {{ $doc->id }},
                        tipe: '{{ addslashes($doc->tipe_dokumen) }}',
                        diunggah: '{{ $doc->updated_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB',
                        fileSize: '{{ $docSizeLabel }}',
                        status: '{{ str_replace('_', ' ', $doc->status) }}',
                        fileUrl: '{{ route('admin.dokumen.download', $doc->id) }}',
                        isPdf: {{ str_ends_with(strtolower($doc->file_path), '.pdf') ? 'true' : 'false' }},
                        catatan: '{{ addslashes($doc->admin_notes ?? '') }}'
                    },
                    @endforeach
                ]
            },
            @endforeach
        ],
        init() {
            const raw = sessionStorage.getItem('rozwia_reopen_doc');
            if (!raw) return;
            sessionStorage.removeItem('rozwia_reopen_doc');
            try {
                const state = JSON.parse(raw);
                const jamaah = this.jamaahList.find(j => j.id === state.jamaahId);
                if (!jamaah) return;
                this.selectedJamaah = jamaah;
                const next = jamaah.dokumens.find(d => d.status === 'Menunggu Verifikasi' && d.id !== state.docId);
                this.selectedDoc = next || jamaah.dokumens.find(d => d.id === state.docId) || jamaah.dokumens[0] || null;
                this.adminNotes = this.selectedDoc ? this.selectedDoc.catatan : '';
                this.modalOpen = true;
            } catch (e) {}
        },
        rememberModal() {
            if (this.selectedJamaah && this.selectedDoc) {
                sessionStorage.setItem('rozwia_reopen_doc', JSON.stringify({
                    jamaahId: this.selectedJamaah.id,
                    docId: this.selectedDoc.id
                }));
            }
        },
        periksaJamaah(jamaah) {
            this.selectedJamaah = jamaah;
            this.selectedDoc = jamaah.dokumens.length > 0 ? jamaah.dokumens[0] : null;
            this.adminNotes = this.selectedDoc ? this.selectedDoc.catatan : '';
            this.modalOpen = true;
        },
        selectDoc(doc) {
            this.selectedDoc = doc;
            this.adminNotes = doc ? doc.catatan : '';
        },
        filteredList() {
            return this.jamaahList.filter(item => {
                let matchFilter = (this.filterStatus === 'semua') ||
                                  (this.filterStatus.toLowerCase() === item.status.toLowerCase());
                let q = this.searchQuery.toLowerCase();
                let matchSearch = q === '' ||
                                  item.nama.toLowerCase().includes(q) ||
                                  item.kode_booking.toLowerCase().includes(q) ||
                                  item.nik.toLowerCase().includes(q);
                return matchFilter && matchSearch;
            });
        }
     }"
     class="space-y-6">

    <!-- Header -->
    <div class="space-y-1">
        <h1 class="text-3xl sm:text-4xl font-bold font-serif text-stone-900 tracking-tight">Verifikasi Dokumen Pribadi</h1>
        <p class="text-xs sm:text-sm text-stone-600 font-serif">
            1 baris per jamaah. Klik baris jamaah atau tombol <strong>Periksa</strong> untuk melihat & memverifikasi seluruh dokumennya.
        </p>
    </div>

    <!-- 5 Kartu Statistik Putih -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 font-serif">
        <!-- 1. Menunggu Verifikasi -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-stone-200/80 shadow-xs flex flex-col justify-between gap-2">
            <span class="text-xs text-stone-700 font-medium block">Menunggu Verifikasi</span>
            <p class="text-xl sm:text-2xl font-bold font-sans tabular-nums text-stone-900">
                {{ $menungguJamaah->count() }} <span class="text-xs font-semibold font-serif text-stone-500">Jamaah</span>
            </p>
        </div>

        <!-- 2. Perlu Diperbaiki -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-stone-200/80 shadow-xs flex flex-col justify-between gap-2">
            <span class="text-xs text-stone-700 font-medium block">Perlu Diperbaiki</span>
            <p class="text-xl sm:text-2xl font-bold font-sans tabular-nums text-amber-700">
                {{ $perluPerbaikanJamaah->count() }} <span class="text-xs font-semibold font-serif text-stone-500">Jamaah</span>
            </p>
        </div>

        <!-- 3. Ditolak -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-stone-200/80 shadow-xs flex flex-col justify-between gap-2">
            <span class="text-xs text-rose-600 font-medium block">Ditolak</span>
            <p class="text-xl sm:text-2xl font-bold font-sans tabular-nums text-rose-700">
                {{ $ditolakJamaah->count() }} <span class="text-xs font-semibold font-serif text-stone-500">Jamaah</span>
            </p>
        </div>

        <!-- 4. Kadaluwarsa -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-stone-200/80 shadow-xs flex flex-col justify-between gap-2">
            <span class="text-xs text-rose-600 font-medium block">Kadaluwarsa</span>
            <p class="text-xl sm:text-2xl font-bold font-sans tabular-nums text-stone-400">
                0 <span class="text-xs font-semibold font-serif text-stone-400">Jamaah</span>
            </p>
        </div>

        <!-- 5. Diverifikasi -->
        <div class="col-span-2 md:col-span-1 bg-white p-4 sm:p-5 rounded-2xl border border-stone-200/80 shadow-xs flex flex-col justify-between gap-2">
            <span class="text-xs text-emerald-700 font-medium block">Diverifikasi</span>
            <p class="text-xl sm:text-2xl font-bold font-sans tabular-nums text-emerald-800">
                {{ $diverifikasiJamaah->count() }} <span class="text-xs font-semibold font-serif text-stone-500">Jamaah</span>
            </p>
        </div>
    </div>

    <!-- Bar Filter Tombol + Search Input -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1 font-serif">
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button"
                    @click="filterStatus = 'semua'"
                    :class="filterStatus === 'semua' ? 'bg-[#0B3B24] text-white' : 'bg-white text-stone-800 border border-stone-800'"
                    class="px-5 py-2 rounded-full text-xs font-bold transition shadow-xs cursor-pointer">
                Semua
            </button>

            <button type="button"
                    @click="filterStatus = 'menunggu verifikasi'"
                    :class="filterStatus === 'menunggu verifikasi' ? 'bg-[#0B3B24] text-white' : 'bg-white text-stone-800 border border-stone-800'"
                    class="px-4 py-2 rounded-full text-xs font-medium transition shadow-xs cursor-pointer">
                Menunggu Verifikasi
            </button>

            <button type="button"
                    @click="filterStatus = 'perlu diperbaiki'"
                    :class="filterStatus === 'perlu diperbaiki' ? 'bg-[#0B3B24] text-white' : 'bg-white text-stone-800 border border-stone-800'"
                    class="px-4 py-2 rounded-full text-xs font-medium transition shadow-xs cursor-pointer">
                Perlu Diperbaiki
            </button>

            <button type="button"
                    @click="filterStatus = 'ditolak'"
                    :class="filterStatus === 'ditolak' ? 'bg-[#0B3B24] text-white' : 'bg-white text-stone-800 border border-stone-800'"
                    class="px-4 py-2 rounded-full text-xs font-medium transition shadow-xs cursor-pointer">
                Ditolak
            </button>

            <button type="button"
                    @click="filterStatus = 'diverifikasi'"
                    :class="filterStatus === 'diverifikasi' ? 'bg-[#0B3B24] text-white' : 'bg-white text-stone-800 border border-stone-800'"
                    class="px-4 py-2 rounded-full text-xs font-medium transition shadow-xs cursor-pointer">
                Diverifikasi
            </button>
        </div>

        <div class="relative w-full sm:w-64 shrink-0 font-sans">
            <span class="absolute left-3.5 top-2.5 text-stone-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text"
                   x-model="searchQuery"
                   placeholder="Cari nama jamaah / NIK..."
                   class="w-full pl-9 pr-4 py-2 bg-white rounded-full border border-stone-200 text-xs text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-[#0B3B24] shadow-xs">
        </div>
    </div>

    <!-- Tabel Jamaah (1 Jamaah = 1 Baris) -->
    <div class="bg-white rounded-[32px] p-6 sm:p-8 shadow-sm border border-stone-200/80 overflow-hidden font-serif">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-stone-200/70 text-[#C98A4B] font-bold tracking-wider text-[11px]">
                        <th class="pb-4 pr-3">JAMAAH</th>
                        <th class="pb-4 px-3">BERKAS</th>
                        <th class="pb-4 px-3">DIUNGGAH</th>
                        <th class="pb-4 px-3 text-center">STATUS DOKUMEN</th>
                        <th class="pb-4 pl-3 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 font-sans text-xs">
                    <template x-for="jamaah in filteredList()" :key="jamaah.id">
                        <tr @click="periksaJamaah(jamaah)" class="hover:bg-stone-50/90 transition cursor-pointer">
                            <!-- Nama Jamaah + Paket -->
                            <td class="py-4 pr-3 font-serif">
                                <div class="text-stone-900 text-xs font-bold" x-text="jamaah.nama"></div>
                                <div class="text-[11px] text-stone-500 font-sans mt-0.5" x-text="jamaah.paket + ' (' + jamaah.kode_booking + ')'"></div>
                            </td>

                            <!-- Ringkasan Berkas -->
                            <td class="py-4 px-3 text-stone-700 font-sans">
                                <span class="font-semibold text-stone-900" x-text="jamaah.totalDocs + ' Berkas'"></span>
                                <span class="text-[11px] text-stone-500 block" x-text="jamaah.verifiedCount + ' / ' + jamaah.totalDocs + ' Terverifikasi'"></span>
                            </td>

                            <!-- Tanggal Diunggah Terbaru -->
                            <td class="py-4 px-3 text-stone-600 font-mono text-[11px]" x-text="jamaah.diunggah"></td>

                            <!-- Status Pill -->
                            <td class="py-4 px-3 text-center">
                                <span :class="{
                                    'bg-[#FEF7E0] text-[#B06000] border border-[#FCE293]': jamaah.status === 'Menunggu Verifikasi',
                                    'bg-[#E6F4EA] text-[#137333] border border-[#A8DAB5]': jamaah.status === 'Diverifikasi',
                                    'bg-[#FCE8E6] text-[#C5221F] border border-[#FAD2CF]': jamaah.status === 'Perlu Diperbaiki' || jamaah.status === 'Ditolak'
                                }" class="inline-block px-3 py-1 rounded-full text-[10px] font-bold">
                                    <span x-text="jamaah.status"></span>
                                </span>
                            </td>

                            <!-- Tombol Aksi Periksa -->
                            <td class="py-4 pl-3 text-center">
                                <button type="button"
                                        @click.stop="periksaJamaah(jamaah)"
                                        class="bg-white hover:bg-[#0B3B24] hover:text-white text-stone-800 border border-stone-400 hover:border-[#0B3B24] px-4 py-1.5 rounded-full text-[11px] font-serif font-medium transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1.25em" height="1.25em" viewBox="0 0 1200 1200" aria-hidden="true" class="shrink-0">
                                        <path d="M0 0h1200v1200H0z" fill="none" />
                                        <path fill="currentColor" d="M779.843 599.925c0 95.331-80.664 172.612-180.169 172.612c-99.504 0-180.168-77.281-180.168-172.612c0-95.332 80.664-172.612 180.168-172.612c99.505-.001 180.169 77.281 180.169 172.612M600 240.521c-103.025.457-209.814 25.538-310.904 73.557C214.038 351.2 140.89 403.574 77.394 468.219C46.208 501.218 6.431 549 0 599.981c.76 44.161 48.13 98.669 77.394 131.763c59.543 62.106 130.786 113.018 211.702 154.179C383.367 931.674 487.712 958.015 600 959.48c103.123-.464 209.888-25.834 310.866-73.557c75.058-37.122 148.243-89.534 211.74-154.179c31.185-32.999 70.962-80.782 77.394-131.763c-.76-44.161-48.13-98.671-77.394-131.764c-59.543-62.106-130.824-112.979-211.74-154.141C816.644 268.36 712.042 242.2 600 240.521m-.076 89.248c156.119 0 282.675 120.994 282.675 270.251S756.043 870.27 599.924 870.27S317.249 749.275 317.249 600.02c0-149.257 126.556-270.251 282.675-270.251" />
                                    </svg>
                                    <span>Periksa (<span x-text="jamaah.totalDocs"></span>)</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredList().length === 0">
                        <td colspan="5" class="py-10 text-center text-stone-400 font-serif text-sm">
                            Belum ada berkas dokumen jamaah yang sesuai filter.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- modal cek dokumen jamaah -->
    <div x-show="modalOpen"
         x-transition.opacity
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs font-serif overflow-y-auto">
        <div @click.away="modalOpen = false" class="bg-white rounded-[32px] p-6 sm:p-7 max-w-3xl w-full shadow-2xl space-y-4 border border-stone-200 my-8 max-h-[92vh] overflow-y-auto">
            <!-- Header Modal -->
            <div class="flex items-start justify-between border-b border-stone-100 pb-3">
                <div>
                    <h3 class="text-xl font-bold text-[#0B3B24]" x-text="'Berkas Jamaah: ' + (selectedJamaah?.nama ?? '')"></h3>
                    <p class="text-xs text-stone-500 font-sans mt-0.5" x-text="(selectedJamaah?.paket ?? '') + ' • Kode: ' + (selectedJamaah?.kode_booking ?? '')"></p>
                </div>
                <button type="button" @click="modalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold text-lg cursor-pointer px-2">✕</button>
            </div>

            <!-- Ringkasan Data Identitas Jamaah (dari Profil) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 bg-stone-50 rounded-2xl border border-stone-200 text-xs font-sans" x-show="selectedJamaah">
                <div>
                    <span class="text-stone-500 block text-[11px]">NIK (KTP)</span>
                    <strong class="text-stone-900" x-text="selectedJamaah?.nik"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block text-[11px]">No. Paspor</span>
                    <strong class="text-stone-900" x-text="selectedJamaah?.passport_number"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block text-[11px]">Jenis Kelamin</span>
                    <strong class="text-stone-900" x-text="selectedJamaah?.jenis_kelamin"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block text-[11px]">No. WhatsApp</span>
                    <strong class="text-stone-900" x-text="selectedJamaah?.no_telepon"></strong>
                </div>
            </div>

            <!-- Tab Daftar Dokumen Milik Jamaah -->
            <div class="space-y-2" x-show="selectedJamaah">
                @php /* Guard jika salah satu berkas berstatus Perlu Diperbaiki / Ditolak */ @endphp
                <div class="flex flex-wrap items-center justify-between gap-2"
                     x-data="{
                        get hasRevisionDoc() {
                            return (selectedJamaah?.dokumens ?? []).some(d => d.status === 'Perlu Diperbaiki' || d.status === 'Ditolak');
                        }
                     }">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#C98A4B]">Pilih Dokumen untuk Diperiksa:</span>
                    <form x-show="selectedJamaah && selectedJamaah.verifiedCount < selectedJamaah.totalDocs"
                          :action="'{{ url('/admin/dokumen/jamaah') }}/' + selectedJamaah?.id + '/verify-all'"
                          method="POST"
                          @submit="if (hasRevisionDoc) { $event.preventDefault(); return false; }"
                          onsubmit="return confirm('Pastikan Anda sudah mengecek semua berkas jamaah ini. Setujui & verifikasi seluruh berkas sekarang?')">
                        @csrf
                        <button type="submit"
                                :disabled="hasRevisionDoc"
                                :title="hasRevisionDoc ? 'Tidak bisa verifikasi semua berkas karena ada berkas berstatus Minta Perbaikan / Ditolak' : 'Verifikasi seluruh berkas sekaligus'"
                                :class="hasRevisionDoc
                                    ? 'bg-stone-200 text-stone-500 cursor-not-allowed border border-stone-300'
                                    : 'bg-emerald-700 hover:bg-emerald-800 text-white cursor-pointer shadow-xs'"
                                class="font-bold px-3.5 py-1.5 rounded-xl text-[11px] transition inline-flex items-center gap-1.5">
                            <x-icon name="check_circle" class="w-4 h-4" />
                            <span>Verifikasi Semua Berkas</span>
                        </button>
                    </form>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="doc in (selectedJamaah?.dokumens ?? [])" :key="doc.id">
                        <button type="button"
                                @click="selectDoc(doc)"
                                :class="selectedDoc?.id === doc.id ? 'bg-[#0B3B24] text-white border-[#0B3B24]' : 'bg-white text-stone-800 border-stone-300 hover:bg-stone-50'"
                                class="px-3.5 py-2 rounded-xl border text-xs font-sans font-medium flex items-center gap-2 transition cursor-pointer shadow-2xs">
                            <span x-text="doc.tipe"></span>
                            <template x-if="doc.status === 'Diverifikasi'">
                                <span class="inline-flex items-center text-emerald-500" title="Terverifikasi">
                                    <x-icon name="check_plain" class="w-4 h-4" />
                                </span>
                            </template>
                            <template x-if="doc.status === 'Perlu Diperbaiki' || doc.status === 'Ditolak'">
                                <span class="inline-flex items-center text-rose-500" title="Minta Perbaikan">
                                    <x-icon name="close" class="w-4 h-4" />
                                </span>
                            </template>
                            <template x-if="doc.status === 'Menunggu Verifikasi'">
                                <span class="inline-flex items-center text-amber-500" title="Menunggu Verifikasi">
                                    <x-icon name="schedule" class="w-4 h-4" />
                                </span>
                            </template>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Form & Pratinjau Dokumen Aktif -->
            <form x-show="selectedDoc" @submit="rememberModal()" :action="'{{ url('/admin/dokumen') }}/' + selectedDoc?.id + '/verify'" method="POST" class="space-y-4 pt-2 border-t border-stone-100">
                @csrf
                <input type="hidden" name="status" :value="actionStatus">

                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-sans">
                    <div>
                        <span class="text-stone-500">Dokumen Aktif:</span>
                        <strong class="text-[#0B3B24] text-sm ml-1" x-text="selectedDoc?.tipe"></strong>
                        <span class="text-stone-400 ml-2" x-text="'Diunggah: ' + (selectedDoc?.diunggah ?? '')"></span>
                        <span class="ml-2 inline-block px-2 py-0.5 rounded-md bg-stone-100 text-stone-700 font-mono text-[10px] font-semibold" x-text="'Ukuran: ' + (selectedDoc?.fileSize ?? '-')"></span>
                    </div>
                    <span :class="{
                        'bg-[#FEF7E0] text-[#B06000] border border-[#FCE293]': selectedDoc?.status === 'Menunggu Verifikasi',
                        'bg-[#E6F4EA] text-[#137333] border border-[#A8DAB5]': selectedDoc?.status === 'Diverifikasi',
                        'bg-[#FCE8E6] text-[#C5221F] border border-[#FAD2CF]': selectedDoc?.status === 'Perlu Diperbaiki' || selectedDoc?.status === 'Ditolak'
                    }" class="px-3 py-1 rounded-full text-[11px] font-bold" x-text="selectedDoc?.status"></span>
                </div>

                <!-- preview dokumen langsung -->
                <div class="rounded-2xl border border-stone-300 bg-stone-100 overflow-hidden relative">
                    <div x-show="!selectedDoc?.isPdf" class="max-h-[340px] overflow-y-auto flex items-center justify-center p-2 bg-stone-900/5">
                        <img :src="selectedDoc?.fileUrl" alt="Berkas Jamaah" class="max-h-[320px] w-auto mx-auto rounded-xl object-contain shadow-sm">
                    </div>

                    <div x-show="selectedDoc?.isPdf" class="h-[340px] w-full">
                        <iframe :src="selectedDoc?.fileUrl" class="w-full h-full rounded-2xl border-0"></iframe>
                    </div>

                    <div class="p-2 bg-white border-t border-stone-200 flex items-center justify-between px-3 text-[11px] font-sans">
                        <span class="text-stone-500">Pratinjau Berkas Asli</span>
                        <a :href="selectedDoc?.fileUrl" target="_blank" class="text-[#0B3B24] font-bold hover:underline flex items-center gap-1">
                            <x-icon name="north_east" class="w-3.5 h-3.5" /> <span>Buka Tab Baru</span>
                        </a>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-stone-700 text-xs">Catatan Perbaikan untuk Jamaah (Dikirim ke Email jika Minta Perbaikan):</label>
                    <textarea name="admin_notes" x-model="adminNotes" rows="2" placeholder="Tuliskan catatan jika berkas perlu diperbaiki oleh jamaah..." class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs font-sans focus:outline-none focus:ring-1 focus:ring-[#0B3B24]"></textarea>
                </div>

                <!-- Tombol Aksi Verifikasi -->
                <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-stone-100">
                    <button type="submit" @click="actionStatus = 'Perlu_Diperbaiki'" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs transition cursor-pointer inline-flex items-center gap-1.5 shadow-sm">
                        <x-icon name="warning" class="w-4 h-4" />
                        <span>Minta Perbaikan</span>
                    </button>
                    <button type="submit" @click="actionStatus = 'Diverifikasi'" class="bg-[#0B3B24] hover:bg-emerald-900 text-white font-bold px-5 py-2.5 rounded-xl text-xs transition cursor-pointer shadow-sm inline-flex items-center gap-1.5">
                        <x-icon name="check_circle" class="w-4 h-4" />
                        <span>Setujui &amp; Verifikasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@extends('layouts.admin')

@section('title', 'Jadwal & Manifest - Admin ROZWIA')

@section('content')
@php
    $readyAccounts = $readiness['readyAccounts'];
    $queuedAccounts = $readiness['queuedAccounts'];
    $tglBerangkatLabel = \App\Helpers\RozwiaHelper::formatDate($selectedJadwal?->tanggal_keberangkatan, 'd/m/Y', 'Menunggu Jadwal');
@endphp

<div class="space-y-6 font-serif"
     x-data="{
        subjekEmail: 'Jadwal dan Manifest Keberangkatan {{ addslashes($selectedPaket?->nama_paket ?? 'Umroh') }} - Rozwia Travel',
        isiPesan: `Assalamu'alaikum Warahmatullahi Wabarakatuh [sapaan] [nama lengkap jamaah],\n\nAlhamdulillah jadwal perjalanan dan manifest keberangkatan ibadah {{ addslashes($selectedPaket?->nama_paket ?? 'Umroh') }} ({{ $tglBerangkatLabel }}) telah kami siapkan dengan lengkap.\n\nSilakan unduh lampiran berkas jadwal penerbangan, hotel, serta pembagian kamar dan bus pada email ini.\n\nMohon hadir di titik kumpul sesuai jadwal keberangkatan. Bila ada pertanyaan, hubungi tim ROZWIA Islamic Travel.\n\nWassalamu'alaikum Wr. Wb.\nTim Operasional ROZWIA Islamic Travel`
     }">

    <!-- header halaman -->
    <div class="bg-white rounded-[24px] border border-stone-200/80 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold font-serif-display text-stone-900 tracking-tight">Jadwal &amp; Manifest</h1>
            <p class="text-xs text-stone-500 font-sans mt-0.5">
                Pengiriman jadwal &amp; manifest dikirim langsung ke seluruh akun dalam paket yang sudah memiliki Surat Kemenag dan Visa Terbit.
            </p>
        </div>

        @if($selectedPaket)
        <div class="shrink-0 flex flex-wrap items-center gap-2 font-sans text-xs">
            <span class="px-3.5 py-2 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold inline-flex items-center gap-1.5">
                <x-icon name="check_circle" class="w-4 h-4 text-emerald-600" />
                <span>{{ $readyAccounts->count() }} Akun Siap Kirim ({{ $readiness['readyJamaahCount'] }} Jamaah)</span>
            </span>
            <span class="px-3.5 py-2 rounded-full bg-rose-50 border border-rose-200 text-rose-700 font-bold inline-flex items-center gap-1.5">
                <x-icon name="schedule" class="w-4 h-4 text-rose-600" />
                <span>{{ $queuedAccounts->count() }} Akun Dalam Antrian</span>
            </span>
        </div>
        @endif
    </div>

    @if($selectedPaket)
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- form broadcast manifest -->
        <div class="lg:col-span-7 bg-white rounded-[28px] border border-stone-200/80 p-6 sm:p-8 shadow-xs space-y-6 font-sans">
            <div class="border-b border-stone-100 pb-4">
                <h2 class="text-lg font-bold font-serif-display text-stone-900">Form Pengiriman Pesan</h2>
                <p class="text-xs text-stone-500">Pilih paket, lengkapi subjek, pesan, serta berkas lampiran untuk dikirim ke semua akun yang memenuhi syarat.</p>
            </div>

            <form action="{{ route('admin.manifest.send') }}" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
                @csrf

                <!-- pilih paket -->
                <div class="space-y-1.5">
                    <label for="paketSelect" class="font-bold text-stone-800 block">Pilih Paket *</label>
                    <select id="paketSelect"
                            name="paket_id"
                            onchange="window.location = '{{ route('admin.manifest') }}?paket_id=' + this.value"
                            class="w-full px-4 py-3 rounded-2xl bg-[#FAF8F5] border border-stone-300 font-bold text-stone-900 focus:outline-none focus:border-[#0B3B24] cursor-pointer">
                        @forelse($pakets as $pkt)
                            @php
                                $firstJ = $pkt->jadwals->sortByDesc('tanggal_keberangkatan')->first();
                            @endphp
                            <option value="{{ $pkt->id }}" @selected($selectedPaket->id === $pkt->id)>
                                {{ $pkt->nama_paket }} ({{ $pkt->kategori }} · {{ \App\Helpers\RozwiaHelper::formatDate($firstJ?->tanggal_keberangkatan, 'd/m/Y', 'Belum terjadwal') }})
                            </option>
                        @empty
                            <option value="">Belum ada paket terdaftar</option>
                        @endforelse
                    </select>
                    <p class="text-[11px] text-stone-500">
                        Tersedia <strong>{{ $readyAccounts->count() }} akun</strong> yang sudah memiliki Surat Kemenag &amp; Visa <strong>Terbit</strong> pada paket ini.
                    </p>
                </div>

                <!-- daftar penerima siap kirim -->
                @if($readyAccounts->isNotEmpty())
                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-2.5">
                    <div class="flex items-center justify-between text-[11px] font-bold text-emerald-900 uppercase tracking-wider">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="check_circle" class="w-4 h-4 text-emerald-700" />
                            <span>Akun Siap Menerima Broadcast:</span>
                        </span>
                        <span>{{ $readyAccounts->count() }} Akun ({{ $readiness['readyJamaahCount'] }} Jamaah)</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($readyAccounts as $ready)
                            <span class="px-2.5 py-1 rounded-full bg-white border border-emerald-200 text-emerald-900 text-[11px] font-semibold">
                                {{ $ready['user']->name }} ({{ $ready['count'] }} Orang)
                            </span>
                        @endforeach
                    </div>
                    <label class="flex items-center gap-2 pt-1 text-[11px] text-emerald-900 font-medium cursor-pointer">
                        <input type="checkbox" name="kirim_ke_semua_anggota" value="1" class="w-4 h-4 rounded accent-[#0B3B24]">
                        <span>Kirim juga salinan ke seluruh alamat email masing-masing anggota jamaah</span>
                    </label>
                </div>
                @else
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] flex items-start gap-2.5">
                    <x-icon name="warning" class="w-4 h-4 text-amber-700 shrink-0 mt-0.5" />
                    <div>
                        <strong class="block">Belum ada akun yang siap dikirim pada paket ini.</strong>
                        Pastikan seluruh anggota pada akun jamaah sudah memiliki <strong>Surat Kemenag = Terbit</strong> dan <strong>Visa = Terbit</strong> di menu Status Surat Kemenag &amp; Visa.
                    </div>
                </div>
                @endif

                <!-- subjek email -->
                <div class="space-y-1.5">
                    <label class="font-bold text-stone-800 block">Subjek / Judul Email *</label>
                    <input type="text" name="subjek" x-model="subjekEmail" required
                           class="w-full px-4 py-3 rounded-2xl bg-white border border-stone-300 font-bold text-stone-900 focus:outline-none focus:border-[#0B3B24]">
                </div>

                <!-- isi pesan -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-stone-800 block">Isi Pesan *</label>
                        <span class="text-[10px] text-stone-400 font-mono">Variabel: [sapaan], [nama lengkap jamaah]</span>
                    </div>
                    <textarea name="pesan" rows="6" x-model="isiPesan" required
                              class="w-full px-4 py-3 rounded-2xl bg-white border border-stone-300 text-stone-800 leading-relaxed focus:outline-none focus:border-[#0B3B24] resize-none"></textarea>
                </div>

                <!-- unggah lampiran -->
                <div class="space-y-2" x-data="{
                    selectedFiles: [],
                    updateFiles(e) {
                        this.selectedFiles = Array.from(e.target.files);
                    },
                    formatSize(bytes) {
                        if (!bytes || bytes === 0) return '0 B';
                        const k = 1024;
                        const sizes = ['B', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                    },
                    resetFiles() {
                        this.selectedFiles = [];
                        $refs.fileInput.value = '';
                    }
                }">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-stone-800 block">
                            Upload Berkas <span class="text-stone-400 font-normal">(PDF / XLSX / DOCX, Maks. 10MB)</span>
                        </label>
                        <button type="button" x-show="selectedFiles.length > 0" @click="resetFiles()" class="text-rose-600 hover:text-rose-800 text-[11px] font-bold cursor-pointer inline-flex items-center gap-1">
                            <x-icon name="close" class="w-3.5 h-3.5" /> <span>Hapus Pilihan</span>
                        </button>
                    </div>

                    <div class="border-2 border-dashed rounded-2xl p-5 text-center transition relative"
                         :class="selectedFiles.length > 0 ? 'border-emerald-500 bg-emerald-50/40' : 'border-stone-300 bg-[#FCFBF8] hover:bg-stone-50 cursor-pointer'">
                        <input type="file"
                               name="lampiran[]"
                               multiple
                               x-ref="fileInput"
                               @change="updateFiles($event)"
                               class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10"
                               title="Pilih berkas lampiran">

                        <div x-show="selectedFiles.length === 0">
                            <x-icon name="cloud_upload" class="w-7 h-7 mx-auto text-stone-400 mb-1.5" />
                            <p class="font-bold text-stone-700 text-xs">Klik untuk memilih berkas jadwal atau manifest</p>
                            <p class="text-[10px] text-stone-400 mt-0.5">Berkas yang diunggah akan dilampirkan otomatis pada email seluruh akun siap kirim</p>
                        </div>

                        <div x-show="selectedFiles.length > 0" class="space-y-3 relative z-20 pointer-events-none">
                            <div class="flex items-center justify-center gap-2 text-emerald-800 font-bold text-xs">
                                <x-icon name="check_circle" class="w-4 h-4 text-emerald-600" />
                                <span x-text="selectedFiles.length + ' Berkas Terpilih & Siap Dikirim'"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-left">
                                <template x-for="(file, i) in selectedFiles" :key="i">
                                    <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white border border-emerald-200 shadow-2xs">
                                        <x-icon name="description" class="w-4 h-4 text-amber-600 shrink-0" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-stone-800 truncate" x-text="file.name"></p>
                                            <span class="text-[10px] text-stone-500 font-mono" x-text="formatSize(file.size)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- tombol kirim manifest -->
                <div class="pt-4 border-t border-stone-100 flex flex-col sm:flex-row items-center gap-3">
                    <button type="submit"
                            @disabled(! $readiness['canSend'])
                            onclick="return confirm('Kirim jadwal dan manifest resmi ke seluruh {{ $readyAccounts->count() }} akun jamaah yang sudah memenuhi syarat pada paket ini?')"
                            class="w-full sm:flex-1 py-3.5 px-6 rounded-2xl font-bold transition flex items-center justify-center gap-2 shadow-md {{ $readiness['canSend'] ? 'bg-[#0B3B24] hover:bg-emerald-900 text-white cursor-pointer' : 'bg-stone-200 text-stone-500 cursor-not-allowed' }}">
                        <x-icon name="send" class="w-4 h-4" />
                        <span>Kirim Semua Jamaah</span>
                    </button>

                    <button type="submit"
                            name="is_test"
                            value="1"
                            onclick="return confirm('Kirim email uji coba ke alamat admin Anda ({{ auth()->user()->email ?? 'admin' }})?')"
                            class="w-full sm:w-auto py-3.5 px-5 rounded-2xl border border-stone-300 bg-white hover:bg-stone-50 text-stone-800 font-bold transition flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <x-icon name="mark_email_read" class="w-4 h-4 text-amber-600" />
                        <span>Uji Coba Email</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- daftar akun antrian -->
        <div class="lg:col-span-5 space-y-5 font-sans">
            <div class="bg-white rounded-[28px] border border-stone-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                    <div>
                        <h3 class="font-bold text-stone-900 font-serif-display text-lg">Akun Dalam Antrian</h3>
                        <p class="text-[11px] text-stone-500">Akun pada paket ini yang belum memenuhi syarat pengiriman manifest.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 font-bold text-[10px]">
                        {{ $queuedAccounts->count() }} Akun
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] uppercase font-bold text-stone-500 border-b border-stone-200">
                                <th class="pb-2.5">Akun</th>
                                <th class="pb-2.5 text-center">Jumlah Jamaah</th>
                                <th class="pb-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($queuedAccounts as $queued)
                            <tr class="hover:bg-stone-50/80">
                                <td class="py-3 pr-2">
                                    <strong class="text-stone-900 block font-serif">{{ $queued['user']->name ?? 'Jamaah' }}</strong>
                                    <span class="text-[10px] text-stone-500 font-mono">{{ $queued['user']->email ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full bg-stone-100 font-bold text-stone-700 text-[10px]">
                                        {{ $queued['count'] }} Orang
                                    </span>
                                </td>
                                <td class="py-3 pl-2 text-right">
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 border border-rose-200 text-[10px] font-bold">
                                        Belum Memenuhi Syarat
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-stone-500 text-xs">
                                    Tidak ada akun dalam antrian. Seluruh akun pada paket ini sudah memenuhi syarat atau belum ada pendaftar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- catatan alur manifest -->
            <div class="bg-[#0B3B24] text-white rounded-[28px] p-6 shadow-sm space-y-3 font-serif">
                <div class="flex items-center gap-2">
                    <x-icon name="info" class="w-5 h-5 text-amber-400" />
                    <h4 class="font-bold text-amber-200 text-sm">Ketentuan Pengiriman Manifest</h4>
                </div>
                <p class="text-xs text-emerald-100/85 leading-relaxed font-sans">
                    Hanya akun yang seluruh anggota jamaahnya berstatus <strong>Surat Kemenag Terbit</strong> dan <strong>Visa Terbit</strong> yang akan menerima email dan notifikasi jadwal manifest saat tombol <strong>Kirim Semua Jamaah</strong> ditekan.
                </p>
            </div>
        </div>

    </div>
    @else
    <div class="rounded-[28px] border border-dashed border-stone-300 bg-white p-12 text-center text-sm text-stone-500 font-sans">
        Belum ada paket perjalanan yang terdaftar di sistem.
    </div>
    @endif

</div>
@endsection

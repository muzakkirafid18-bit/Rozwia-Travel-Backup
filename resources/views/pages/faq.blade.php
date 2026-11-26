@extends('layouts.app')

@section('title', 'Pertanyaan Umum (FAQ) - Rozwia Islamic Travel')

@section('content')
<div class="w-full bg-[#EAE0D0] min-h-screen pb-24 pt-28 sm:pt-32 font-serif"
     x-data="{
        activeCategory: 'semua',
        openIndex: 0,
        faqs: [
            {
                category: 'pendaftaran',
                label: 'PENDAFTARAN',
                q: 'Bagaimana cara mendaftar paket?',
                a: 'Pilih paket, jenis layanan, jadwal keberangkatan, dan tipe kamar di halaman detail paket. Lalu isi data setiap jamaah dan tekan Daftar. Tagihan DP akan terbit dan dapat dikonfirmasi melalui WhatsApp Admin.'
            },
            {
                category: 'pendaftaran',
                label: 'PENDAFTARAN',
                q: 'Apakah bisa mendaftarkan lebih dari satu jamaah sekaligus?',
                a: 'Bisa. Anda cukup mengatur Jumlah Jamaah pada formulir pendaftaran, lalu mengisi nama lengkap seluruh anggota jamaah yang dibawa melalui popup yang tersedia. Semua anggota akan dikelola dalam 1 akun penanggung jawab.'
            },
            {
                category: 'pendaftaran',
                label: 'PENDAFTARAN',
                q: 'Apakah perbedaan tipe kamar Quad, Triple, dan Double?',
                a: 'Quad adalah 1 kamar diisi 4 orang jamaah, Triple untuk 3 orang jamaah, dan Double untuk 2 orang jamaah (cocok bagi pasangan suami-istri atau keluarga).'
            },
            {
                category: 'pembayaran',
                label: 'PEMBAYARAN',
                q: 'Bagaimana cara membayar DP dan pelunasan?',
                a: 'Transfer ke rekening resmi PT Pangrango Berkah Digital (BSI 7123456789), lalu klik tombol Konfirmasi via WhatsApp Admin (0851-2297-8237) untuk mengirimkan bukti transfer Anda.'
            },
            {
                category: 'pembayaran',
                label: 'PEMBAYARAN',
                q: 'Mengapa akun saya belum aktif?',
                a: 'Akun baru akan diaktifkan secara otomatis oleh Admin setelah pembayaran Uang Muka (DP) Anda dikonfirmasi dan diverifikasi melalui WhatsApp.'
            },
            {
                category: 'dokumen',
                label: 'DOKUMEN',
                q: 'Dokumen apa saja yang harus disiapkan?',
                a: 'Dokumen utama yang wajib diunggah di Portal Jamaah adalah e-KTP, Kartu Keluarga (KK), Paspor Asli (minimal berlaku 7 bulan), Pas Foto 4x6 latar belakang putih, serta Buku Nikah atau Akta Lahir sebagai bukti mahram.'
            },
            {
                category: 'dokumen',
                label: 'DOKUMEN',
                q: 'Dokumen saya ditolak, apa yang harus dilakukan?',
                a: 'Buka menu Dokumen di Portal Jamaah, baca catatan perbaikan dari Admin (misalnya foto buram atau terpotong), lalu unggah ulang foto/scan dokumen yang lebih jelas.'
            },
            {
                category: 'keberangkatan',
                label: 'KEBERANGKATAN',
                q: 'Bagaimana memantau perjalanan saya?',
                a: 'Setelah login ke Portal Jamaah, buka menu Status Perjalanan untuk memantau perkembangan mulai dari persiapan berkas, manasik, penerbangan, hingga tiba kembali di tanah air.'
            },
            {
                category: 'keberangkatan',
                label: 'KEBERANGKATAN',
                q: 'Bisakah meminta layanan khusus, misalnya kursi roda?',
                a: 'Tentu bisa. Silakan informasikan kebutuhan khusus seperti kursi roda atau pendampingan lansia kepada tim Admin kami melalui WhatsApp sebelum jadwal keberangkatan.'
            }
        ],
        filteredFaqs() {
            if (this.activeCategory === 'semua') return this.faqs;
            return this.faqs.filter(f => f.category === this.activeCategory);
        }
     }">

    <!-- banner faq -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
        <div class="relative rounded-[24px] bg-[#0B3B24] overflow-hidden shadow-lg p-8 sm:p-12">
            <div class="absolute inset-0 opacity-35 bg-cover bg-center" style="background-image: url('{{ asset('images/about us.jpg') }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[#0B3B24]/90 via-[#0B3B24]/60 to-transparent"></div>

            <div class="relative z-10 space-y-2">
                <p class="text-xs text-amber-300 tracking-widest uppercase">
                    <a href="/" class="hover:underline">Beranda</a> &gt; <span class="text-white font-medium">Pertanyaan Umum</span>
                </p>
                <h1 class="text-3xl sm:text-5xl font-bold text-white tracking-tight">
                    Pertanyaan yang Sering Diajukan
                </h1>
                <p class="text-emerald-100/90 text-xs sm:text-base max-w-2xl leading-relaxed">
                    Temukan jawaban seputar pendaftaran, pembayaran, dokumen keberangkatan, dan layanan perjalanan bersama ROZWIA.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- filter kategori -->
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" @click="activeCategory = 'semua'; openIndex = 0"
                    :class="activeCategory === 'semua' ? 'bg-[#0B3B24] text-white' : 'bg-[#FBF9F5] text-[#0B3B24] hover:bg-emerald-50'"
                    class="border border-[#0B3B24] text-xs sm:text-sm font-bold px-6 py-2 rounded-full transition cursor-pointer">
                Semua
            </button>
            <button type="button" @click="activeCategory = 'pendaftaran'; openIndex = 0"
                    :class="activeCategory === 'pendaftaran' ? 'bg-[#0B3B24] text-white' : 'bg-[#FBF9F5] text-[#0B3B24] hover:bg-emerald-50'"
                    class="border border-[#0B3B24] text-xs sm:text-sm font-bold px-6 py-2 rounded-full transition cursor-pointer">
                Pendaftaran
            </button>
            <button type="button" @click="activeCategory = 'pembayaran'; openIndex = 0"
                    :class="activeCategory === 'pembayaran' ? 'bg-[#0B3B24] text-white' : 'bg-[#FBF9F5] text-[#0B3B24] hover:bg-emerald-50'"
                    class="border border-[#0B3B24] text-xs sm:text-sm font-bold px-6 py-2 rounded-full transition cursor-pointer">
                Pembayaran
            </button>
            <button type="button" @click="activeCategory = 'dokumen'; openIndex = 0"
                    :class="activeCategory === 'dokumen' ? 'bg-[#0B3B24] text-white' : 'bg-[#FBF9F5] text-[#0B3B24] hover:bg-emerald-50'"
                    class="border border-[#0B3B24] text-xs sm:text-sm font-bold px-6 py-2 rounded-full transition cursor-pointer">
                Dokumen
            </button>
            <button type="button" @click="activeCategory = 'keberangkatan'; openIndex = 0"
                    :class="activeCategory === 'keberangkatan' ? 'bg-[#0B3B24] text-white' : 'bg-[#FBF9F5] text-[#0B3B24] hover:bg-emerald-50'"
                    class="border border-[#0B3B24] text-xs sm:text-sm font-bold px-6 py-2 rounded-full transition cursor-pointer">
                Keberangkatan
            </button>
        </div>

        <!-- daftar pertanyaan -->
        <div class="space-y-4">
            <template x-for="(item, idx) in filteredFaqs()" :key="item.q">
                <div class="bg-[#FBF9F5] rounded-2xl border border-[#0B3B24]/80 shadow-xs overflow-hidden transition">
                    <button type="button" @click="openIndex = (openIndex === idx ? -1 : idx)"
                            class="w-full px-6 py-4 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-stone-50/80 transition">
                        <div class="space-y-1">
                            <span class="block text-[10px] font-bold tracking-widest uppercase text-[#C96B00]" x-text="item.label"></span>
                            <h3 class="text-sm sm:text-base font-bold text-[#0B3B24]" x-text="item.q"></h3>
                        </div>
                        <span class="text-[#0B3B24] transition-transform duration-200 shrink-0" :class="openIndex === idx ? 'rotate-180' : ''">
                            <x-icon name="keyboard_arrow_down" class="w-5 h-5" />
                        </span>
                    </button>
                    <div x-show="openIndex === idx" x-collapse class="px-6 pb-5 pt-1 text-xs sm:text-sm text-stone-700 leading-relaxed border-t border-stone-200/60">
                        <p x-text="item.a"></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- banner cta bawah -->
        <div class="bg-[#0B3B24] rounded-[28px] p-8 sm:p-10 shadow-xl flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="space-y-2 max-w-xl">
                <h2 class="text-2xl sm:text-3xl font-bold text-white">Pertanyaan Anda belum terjawab?</h2>
                <p class="text-emerald-100/85 text-xs sm:text-sm leading-relaxed">
                    Tim kami siap membantu melalui WhatsApp pada jam operasional (08.00 - 21.00 WIB).
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('paket.umroh') }}" class="bg-[#F59100] hover:bg-[#e08500] text-[#0B3B24] font-bold text-xs sm:text-sm px-7 py-3 rounded-full shadow-md transition">
                    Daftar Paket
                </a>
                <a href="https://wa.me/6285122978237?text=Assalamualaikum%20ROZWIA,%20saya%20memiliki%20pertanyaan%20seputar%20paket%20ibadah." target="_blank" class="bg-[#25D366] hover:bg-[#20ba5a] text-white font-bold text-xs sm:text-sm px-7 py-3 rounded-full shadow-md transition inline-flex items-center justify-center gap-2">
                    @include('partials.wa-icon', ['size' => '1.25em'])
                    <span>Chat Via WhatsApp</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

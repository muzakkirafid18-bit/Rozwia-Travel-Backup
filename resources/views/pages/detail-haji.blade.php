@extends('layouts.app')

@section('title', $package['name'] . ' - Rozwia Islamic Travel')

@section('content')

<!-- wrapper halaman -->
<div class="w-full bg-[#F4EFE6] min-h-screen pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <!-- hero detail haji -->
        <div class="bg-[#0B3B24] rounded-[36px] overflow-hidden shadow-xl text-white p-6 sm:p-12 relative">
            <div class="absolute inset-0 opacity-20 bg-cover bg-center pointer-events-none" style="background-image: url('{{ $package['image'] }}')"></div>
            
            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <!-- Kolom Kiri: Informasi Utama -->
                <div class="lg:col-span-7 space-y-6">
                    <span class="inline-block bg-[#E5D3B3] text-emerald-950 font-serif text-xs font-bold px-3.5 py-1 rounded-full shadow-xs">
                        {{ $package['badge'] ?? 'PAKET HAJI' }}
                    </span>
                    
                    <div class="space-y-2">
                        <h1 class="text-3xl sm:text-5xl font-bold font-serif tracking-tight text-white">
                            {{ $package['name'] }}
                        </h1>
                        <p class="text-emerald-100/90 text-sm sm:text-base font-serif leading-relaxed">
                            {{ $package['description'] }}
                        </p>
                    </div>

                    <!-- Statistik Pill -->
                    <div class="grid grid-cols-3 gap-3 pt-2 max-w-lg">
                        <div class="bg-emerald-900/60 border border-emerald-700/50 rounded-2xl p-3 text-center">
                            <span class="block text-[10px] text-emerald-300 uppercase font-serif">Durasi</span>
                            <span class="text-sm font-bold font-serif text-white">{{ $package['duration'] }}</span>
                        </div>
                        <div class="bg-emerald-900/60 border border-emerald-700/50 rounded-2xl p-3 text-center">
                            <span class="block text-[10px] text-emerald-300 uppercase font-serif">Maskapai</span>
                            <span class="text-sm font-bold font-serif text-white truncate">{{ $package['airline'] }}</span>
                        </div>
                        <div class="bg-emerald-900/60 border border-emerald-700/50 rounded-2xl p-3 text-center">
                            <span class="block text-[10px] text-emerald-300 uppercase font-serif">Kuota</span>
                            <span class="text-sm font-bold font-serif text-amber-300">Tersedia</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <span class="text-xs text-emerald-200 block font-serif uppercase tracking-wider">Mulai dari</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl sm:text-4xl font-extrabold text-amber-400 font-serif">{{ $package['price'] }}</span>
                            <span class="text-xs text-emerald-200 font-serif">/ Jamaah</span>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Gambar Banner -->
                <div class="lg:col-span-5">
                    <div class="relative h-64 sm:h-80 rounded-3xl overflow-hidden shadow-2xl border-4 border-white/10">
                        <img src="{{ (isset($dbPackage) && $dbPackage?->gambar_utama) ? $package['image'] : asset('images/detail.jpg') }}" alt="{{ $package['name'] }}" onerror="this.onerror=null;this.src='{{ asset('images/detail.jpg') }}';" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>


        <!-- tentang paket -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-stone-200/80 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            <div class="md:col-span-8 space-y-2.5">
                <h3 class="text-lg font-bold text-[#0B3B24] font-serif flex items-center gap-2">
                    <x-icon name="box_edit" class="w-5 h-5 text-[#B78616]" />
                    <span>Tentang Paket</span>
                </h3>
                <p class="text-stone-600 text-xs sm:text-sm leading-relaxed font-serif">
                    {{ $package['description'] }}
                </p>
            </div>
            <div class="md:col-span-4 border-t md:border-t-0 md:border-l border-stone-200 pt-4 md:pt-0 md:pl-6 text-stone-700 text-xs sm:text-sm font-serif italic">
                "Menunaikan Ibadah Haji dengan tenang bersama rozwia."
            </div>
        </div>

        <!-- konten detail & form daftar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- rangkaian ibadah haji -->
            <div class="lg:col-span-7 space-y-5">
                <h3 class="text-xl font-bold text-[#0B3B24] font-serif flex items-center gap-2">
                    <x-icon name="travel" class="w-5 h-5 text-[#B78616]" />
                    <span>Rangkaian Ibadah Haji</span>
                </h3>

                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-stone-200/80 space-y-4">
                    @php
                        $hajiSteps = [
                            ['num' => '1', 'title' => 'Keberangkatan dari Indonesia', 'desc' => 'Jamaah memulai perjalanan menuju Tanah Suci sesuai jadwal penerbangan yang telah ditentukan.'],
                            ['num' => '2', 'title' => 'Tiba di Arab Saudi', 'desc' => 'Jamaah tiba dan melanjutkan perjalanan menuju hotel untuk beristirahat serta mempersiapkan ibadah.'],
                            ['num' => '3', 'title' => 'Ihram dan Niat Haji', 'desc' => 'Jamaah mengenakan ihram dan melaksanakan niat Haji sesuai ketentuan.'],
                            ['num' => '4', 'title' => 'Wukuf di Arafah', 'desc' => 'Jamaah melaksanakan wukuf di Arafah pada tanggal 9 Dzulhijjah.'],
                            ['num' => '5', 'title' => 'Mabit di Muzdalifah', 'desc' => 'Jamaah bermalam di Muzdalifah setelah menyelesaikan wukuf.'],
                            ['num' => '6', 'title' => 'Mabit di Mina dan Melontar Jumrah', 'desc' => 'Jamaah menjalankan rangkaian Ibadah di Mina, termasuk melontar jumrah.'],
                            ['num' => '7', 'title' => 'Penyelesaian Ibadah Dan Kepulangan', 'desc' => 'Jamaah menyelesaikan rangkaian ibadah Haji dan kembali ke Indonesia sesuai jadwal.'],
                        ];
                    @endphp

                    @foreach($hajiSteps as $step)
                    <div class="flex items-start gap-4 pb-4 border-b border-stone-100 last:border-b-0 last:pb-0">
                        <span class="w-7 h-7 rounded-full bg-[#0B3B24] text-white text-xs font-bold flex items-center justify-center shrink-0 font-serif">
                            {{ $step['num'] }}
                        </span>
                        <div class="space-y-1">
                            <h4 class="font-bold text-[#0B3B24] text-sm font-serif">{{ $step['title'] }}</h4>
                            <p class="text-xs text-stone-600 font-serif leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- form pendaftaran paket -->
            <div class="lg:col-span-5 lg:sticky lg:top-28 space-y-6"
                 x-data="{
                    jumlahJamaah: 1,
                    anggotaList: [],
                    anggotaGenders: [],
                    showAnggotaModal: false,
                    anggotaConfirmed: false,
                    isSubmitting: false,
                    syncAnggotaCount() {
                        let count = Math.max(1, parseInt(this.jumlahJamaah) || 1);
                        let needed = count - 1;
                        while (this.anggotaList.length < needed) {
                            this.anggotaList.push('');
                            this.anggotaGenders.push('Laki-laki');
                        }
                        if (this.anggotaList.length > needed) {
                            this.anggotaList = this.anggotaList.slice(0, needed);
                            this.anggotaGenders = this.anggotaGenders.slice(0, needed);
                        }
                    },
                    handleSubmit(e) {
                        if (this.isSubmitting) return;
                        this.syncAnggotaCount();
                        if (this.jumlahJamaah > 1 && !this.anggotaConfirmed) {
                            this.showAnggotaModal = true;
                            return;
                        }
                        this.isSubmitting = true;
                        this.$refs.bookingForm.submit();
                    },
                    confirmAnggotaAndSubmit() {
                        if (this.isSubmitting) return;
                        this.anggotaConfirmed = true;
                        this.showAnggotaModal = false;
                        this.isSubmitting = true;
                        this.$nextTick(() => {
                            this.$refs.bookingForm.submit();
                        });
                    }
                 }"
                 x-effect="document.body.classList.toggle('overflow-hidden', showAnggotaModal)">
                <div id="form-pendaftaran" class="bg-[#0B3B24] rounded-3xl p-6 sm:p-8 shadow-xl border border-emerald-900 text-white space-y-6 font-sans scroll-mt-28">
                    
                    <div class="space-y-2 border-b border-emerald-800 pb-4">
                        <h3 class="text-xl font-bold text-white tracking-tight">Daftar Sekarang</h3>
                        <p class="text-xs text-emerald-200 leading-relaxed">Pilih tipe kamar dan lengkapi data jamaah untuk memesan <strong>{{ $package['name'] }}</strong>.</p>
                    </div>

                    <form x-ref="bookingForm" @submit.prevent="handleSubmit($event)" action="{{ route('booking.store', $package['id']) }}" method="POST" class="space-y-4 font-sans">
                        @csrf
                        <input type="hidden" name="jadwal_id" value="{{ $dbPackage?->jadwals?->first()?->id ?? 1 }}">

                        <!-- Pilihan Tipe Kamar -->
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-amber-300">Pilih tipe kamar yang sesuai</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="group relative cursor-pointer block kamar-radio-label">
                                    <input type="radio" name="tipe_kamar" value="double" class="peer sr-only" checked>
                                    <div class="kamar-box p-2.5 rounded-xl border border-emerald-700/80 bg-emerald-900/60 text-center transition-all duration-200 hover:border-amber-400 hover:bg-emerald-800/80 hover:shadow-md">
                                        <span class="block text-[9px] font-extrabold tracking-wider text-emerald-300 transition-colors uppercase">DOUBLE (2 ORANG)</span>
                                        <span class="block text-xs font-bold text-white transition-colors mt-0.5">{{ $kamarPrices['double']['formatted'] }}</span>
                                    </div>
                                </label>
                                <label class="group relative cursor-pointer block kamar-radio-label">
                                    <input type="radio" name="tipe_kamar" value="triple" class="peer sr-only">
                                    <div class="kamar-box p-2.5 rounded-xl border border-emerald-700/80 bg-emerald-900/60 text-center transition-all duration-200 hover:border-amber-400 hover:bg-emerald-800/80 hover:shadow-md">
                                        <span class="block text-[9px] font-extrabold tracking-wider text-emerald-300 transition-colors uppercase">TRIPLE (3 ORANG)</span>
                                        <span class="block text-xs font-bold text-white transition-colors mt-0.5">{{ $kamarPrices['triple']['formatted'] }}</span>
                                    </div>
                                </label>
                                <label class="group relative cursor-pointer block kamar-radio-label">
                                    <input type="radio" name="tipe_kamar" value="quad" class="peer sr-only">
                                    <div class="kamar-box p-2.5 rounded-xl border border-emerald-700/80 bg-emerald-900/60 text-center transition-all duration-200 hover:border-amber-400 hover:bg-emerald-800/80 hover:shadow-md">
                                        <span class="block text-[9px] font-extrabold tracking-wider text-emerald-300 transition-colors uppercase">QUAD (4 ORANG)</span>
                                        <span class="block text-xs font-bold text-white transition-colors mt-0.5">{{ $kamarPrices['quad']['formatted'] }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Tanggal Keberangkatan -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-emerald-200">TANGGAL KEBERANGKATAN</label>
                            <input type="text" readonly value="{{ $package['jadwal'] }}" class="w-full bg-emerald-900/80 border border-emerald-700 text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none">
                        </div>

                        <!-- Nama Anda -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-emerald-200">NAMA ANDA</label>
                            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Nama lengkap sesuai paspor" class="w-full bg-emerald-900/80 border border-emerald-700 text-white placeholder-emerald-400/60 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-amber-400">
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-emerald-200">JENIS KELAMIN *</label>
                            <div class="grid grid-cols-2 gap-2.5">
                                <label class="group relative cursor-pointer block kamar-radio-label">
                                    <input type="radio" name="jenis_kelamin" value="Laki-laki" class="peer sr-only" @checked(old('jenis_kelamin', 'Laki-laki') === 'Laki-laki') required>
                                    <div class="kamar-box p-2.5 rounded-xl border border-emerald-700/80 bg-emerald-900/60 text-center transition-all duration-200 hover:border-amber-400 hover:bg-emerald-800/80">
                                        <span class="block text-xs font-bold text-white transition-colors">Laki-laki</span>
                                    </div>
                                </label>
                                <label class="group relative cursor-pointer block kamar-radio-label">
                                    <input type="radio" name="jenis_kelamin" value="Perempuan" class="peer sr-only" @checked(old('jenis_kelamin') === 'Perempuan') required>
                                    <div class="kamar-box p-2.5 rounded-xl border border-emerald-700/80 bg-emerald-900/60 text-center transition-all duration-200 hover:border-amber-400 hover:bg-emerald-800/80">
                                        <span class="block text-xs font-bold text-white transition-colors">Perempuan</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Nomor WhatsApp -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-emerald-200">NOMOR WHATSAPP *</label>
                            <div class="flex items-center bg-emerald-900/80 border border-emerald-700 rounded-xl overflow-hidden focus-within:border-amber-400">
                                <span class="px-3.5 py-2.5 bg-emerald-950/70 text-amber-300 font-bold text-xs border-r border-emerald-700 select-none">+62</span>
                                <input type="tel"
                                       name="nomor_telepon"
                                       value="{{ old('nomor_telepon') }}"
                                       required
                                       inputmode="numeric"
                                       pattern="(?:\+?62|0)?[0-9\-\s]{8,15}"
                                       title="Masukkan minimal 8 angka nomor WhatsApp (contoh: 81234567890)"
                                       oninput="this.value = this.value.replace(/[^0-9\-\s]/g, '')"
                                       placeholder="812-3456-7890"
                                       class="w-full bg-transparent text-white placeholder-emerald-400/60 px-3.5 py-2.5 text-xs focus:outline-none">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-emerald-200">EMAIL</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@gmail.com" class="w-full bg-emerald-900/80 border border-emerald-700 text-white placeholder-emerald-400/60 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-amber-400">
                        </div>

                        <!-- Jumlah Jamaah -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-emerald-200">JUMLAH JAMAAH</label>
                            <input type="number"
                                   name="jumlah_jamaah"
                                   min="1"
                                   max="50"
                                   x-model.number="jumlahJamaah"
                                   @wheel.prevent
                                   @change="anggotaConfirmed = false; syncAnggotaCount(); if(jumlahJamaah > 1) showAnggotaModal = true;"
                                   class="w-full bg-emerald-900/80 border border-emerald-700 text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-amber-400 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <div x-show="jumlahJamaah > 1" x-cloak class="mt-2 p-2.5 rounded-xl bg-emerald-900/60 border border-emerald-700/80 flex items-center justify-between gap-2 text-[11px]">
                                <span class="text-amber-300">👥 Membawa <strong x-text="jumlahJamaah - 1"></strong> anggota tambahan</span>
                                <button type="button" @click="syncAnggotaCount(); showAnggotaModal = true" class="bg-amber-500 text-emerald-950 font-bold px-2.5 py-1 rounded-lg hover:bg-amber-400 transition cursor-pointer">
                                    ✎ Isi Nama
                                </button>
                            </div>
                        </div>

                        <!-- Hidden Inputs untuk Anggota Jamaah -->
                        <template x-for="(nama, idx) in anggotaList" :key="idx">
                            <div>
                                <input type="hidden" name="anggota_jamaah[]" :value="nama">
                                <input type="hidden" name="anggota_gender[]" :value="anggotaGenders[idx] || 'Laki-laki'">
                            </div>
                        </template>

                        <!-- Tombol Aksi Menumpuk Vertikal & WhatsApp Hijau -->
                        <div class="pt-2 space-y-3">
                            <button type="submit"
                                    :disabled="isSubmitting"
                                    :class="isSubmitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-amber-400 active:scale-98 hover:shadow-lg cursor-pointer'"
                                    class="w-full bg-amber-500 text-emerald-950 font-bold py-3.5 px-4 rounded-xl shadow-md transition-all duration-200 text-xs uppercase tracking-wider text-center">
                                <span x-text="isSubmitting ? 'Memproses Pendaftaran...' : 'Daftar Paket'">Daftar Paket</span>
                            </button>
                            <a href="https://wa.me/6285122978237?text=Assalamualaikum%20ROZWIA,%20saya%20ingin%20berkonsultasi%20mengenai%20paket%20{{ urlencode($package['name']) }}." target="_blank" class="w-full bg-[#25D366] hover:bg-[#20ba5a] text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition text-xs flex items-center justify-center gap-2 text-center">
                                <span>💬</span> Chat Via WhatsApp
                            </a>
                        </div>
                    </form>

                </div>

                <!-- modal isi nama anggota rombongan -->
                <template x-teleport="body">
                    <div x-show="showAnggotaModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs font-serif overflow-y-auto">
                        <div class="bg-white text-stone-800 rounded-[32px] p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4 border border-stone-200 my-auto max-h-[85vh] flex flex-col">
                            <div class="flex items-center justify-between border-b border-stone-100 pb-3 shrink-0">
                                <div>
                                    <h3 class="text-lg font-bold text-[#0B3B24]">Daftar Nama Jamaah yang Dibawa</h3>
                                    <p class="text-[11px] text-stone-500 mt-0.5">Silakan isi nama lengkap untuk setiap anggota jamaah sesuai paspor/KTP.</p>
                                </div>
                                <button type="button" @click="showAnggotaModal = false" class="text-stone-400 hover:text-stone-700 font-bold text-lg cursor-pointer">✕</button>
                            </div>

                            <div class="space-y-3 overflow-y-auto pr-1 text-xs flex-1">
                                <template x-for="(nama, idx) in anggotaList" :key="idx">
                                    <div class="space-y-2 p-3 rounded-2xl bg-stone-50 border border-stone-200">
                                        <div class="space-y-1">
                                            <label class="font-bold text-[#0B3B24] block" x-text="'Nama Lengkap Jamaah ke-' + (idx + 2)"></label>
                                            <input type="text" x-model="anggotaList[idx]" :placeholder="'Masukkan nama lengkap jamaah ke-' + (idx + 2)" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-stone-900 focus:outline-none focus:border-[#0B3B24]">
                                        </div>
                                        <div class="space-y-1">
                                            <span class="font-bold text-stone-700 text-[11px] block">Jenis Kelamin</span>
                                            <div class="grid grid-cols-2 gap-2">
                                                <label class="cursor-pointer">
                                                    <input type="radio" :name="'modal_gender_' + idx" value="Laki-laki" x-model="anggotaGenders[idx]" class="peer sr-only">
                                                    <span class="block text-center py-2 px-3 rounded-xl border border-stone-300 bg-white font-semibold text-stone-700 peer-checked:bg-[#0B3B24] peer-checked:text-white peer-checked:border-[#0B3B24] transition">Laki-laki</span>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" :name="'modal_gender_' + idx" value="Perempuan" x-model="anggotaGenders[idx]" class="peer sr-only">
                                                    <span class="block text-center py-2 px-3 rounded-xl border border-stone-300 bg-white font-semibold text-stone-700 peer-checked:bg-[#0B3B24] peer-checked:text-white peer-checked:border-[#0B3B24] transition">Perempuan</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-stone-100 shrink-0">
                                <button type="button" @click="showAnggotaModal = false" class="px-4 py-2.5 rounded-xl border border-stone-300 font-bold text-stone-600 text-xs hover:bg-stone-100 transition cursor-pointer">
                                    Tutup
                                </button>
                                <button type="button"
                                        @click="confirmAnggotaAndSubmit()"
                                        :disabled="isSubmitting"
                                        :class="isSubmitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-emerald-900 cursor-pointer'"
                                        class="bg-[#0B3B24] text-white font-bold px-5 py-2.5 rounded-xl text-xs transition shadow-md">
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Simpan & Daftar Sekarang →'">Simpan &amp; Daftar Sekarang →</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>

        <!-- jenis layanan haji -->
        <div class="space-y-6">
            <h3 class="text-xl font-bold text-[#0B3B24] font-serif flex items-center gap-2">
                <x-icon name="volunteer_activism" class="w-5 h-5 text-[#B78616]" />
                <span>Jenis Layanan Haji</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-stone-200 space-y-2.5">
                    <div class="relative h-36 rounded-2xl overflow-hidden">
                        <img src="{{ asset('images/haji reguler.jpg') }}" alt="Haji Reguler" class="w-full h-full object-cover">
                    </div>
                    <h4 class="font-bold text-[#0B3B24] text-base font-serif">Haji Reguler</h4>
                    <p class="text-xs text-stone-600 font-serif leading-relaxed">Program haji reguler dengan layanan standar pemerintah.</p>
                </div>
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-stone-200 space-y-2.5">
                    <div class="relative h-36 rounded-2xl overflow-hidden">
                        <img src="{{ asset('images/haji plus.jpg') }}" alt="Haji Plus" class="w-full h-full object-cover">
                    </div>
                    <h4 class="font-bold text-[#0B3B24] text-base font-serif">Haji Plus</h4>
                    <p class="text-xs text-stone-600 font-serif leading-relaxed">Layanan haji dengan fasilitas lebih eksklusif.</p>
                </div>
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-stone-200 space-y-2.5">
                    <div class="relative h-36 rounded-2xl overflow-hidden">
                        <img src="{{ asset('images/furoda.jpg') }}" alt="Haji Furoda" class="w-full h-full object-cover">
                    </div>
                    <h4 class="font-bold text-[#0B3B24] text-base font-serif">Haji Furoda</h4>
                    <p class="text-xs text-stone-600 font-serif leading-relaxed">Program haji khusus dengan kuota terbatas.</p>
                </div>
            </div>
        </div>

        <!-- info kamar, hotel & fasilitas -->
        <div class="space-y-6">
            <h3 class="text-xl font-bold text-[#0B3B24] font-serif flex items-center gap-2">
                <x-icon name="night_shelter" class="w-5 h-5 text-[#B78616]" />
                <span>Pilihan Kamar dan Hotel</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-stone-200/80 space-y-3">
                    <div class="relative h-52 rounded-2xl overflow-hidden">
                        <img src="{{ $package['hotel_makkah_image'] ?? asset('images/sheraton.jpg') }}" alt="Hotel Makkah" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-[#0B3B24] font-serif">{{ $package['hotel_makkah'] }}</h4>
                        <p class="text-xs text-stone-500 font-serif">Hotel Bintang 5</p>
                        <p class="text-xs text-stone-600 font-serif mt-1">Lokasi: Makkah (± 150 Meter dari Masjidil Haram)</p>
                    </div>
                </div>
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-stone-200/80 space-y-3">
                    <div class="relative h-52 rounded-2xl overflow-hidden">
                        <img src="{{ $package['hotel_madinah_image'] ?? asset('images/crowne.jpg') }}" alt="Hotel Madinah" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-[#0B3B24] font-serif">{{ $package['hotel_madinah'] }}</h4>
                        <p class="text-xs text-stone-500 font-serif">Hotel Bintang 5</p>
                        <p class="text-xs text-stone-600 font-serif mt-1">Lokasi: Madinah (± 100 Meter dari Masjid Nabawi)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200 space-y-4">
                <h4 class="text-base font-bold text-[#0B3B24] font-serif flex items-center gap-2">
                    <x-icon name="checked_bag" class="w-5 h-5 text-[#B78616]" />
                    <span>Fasilitas Paket</span>
                </h4>
                <ul class="space-y-2 text-xs font-serif text-stone-700">
                    @foreach($package['fasilitas'] as $fasilitas)
                    <li class="flex items-center gap-2">
                        <x-icon name="check_circle" class="w-4 h-4 text-emerald-700 shrink-0" />
                        <span>{{ $fasilitas }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200 space-y-4">
                <h4 class="text-base font-bold text-[#0B3B24] font-serif flex items-center gap-2">
                    <x-icon name="edit_note" class="w-5 h-5 text-[#B78616]" />
                    <span>Persyaratan Dokumen</span>
                </h4>
                <ul class="space-y-2 text-xs font-serif text-stone-700">
                    <li class="flex items-center gap-2"><x-icon name="check_circle" class="w-4 h-4 text-emerald-700 shrink-0" /> Paspor</li>
                    <li class="flex items-center gap-2"><x-icon name="check_circle" class="w-4 h-4 text-emerald-700 shrink-0" /> KTP</li>
                    <li class="flex items-center gap-2"><x-icon name="check_circle" class="w-4 h-4 text-emerald-700 shrink-0" /> Kartu Keluarga</li>
                    <li class="flex items-center gap-2"><x-icon name="check_circle" class="w-4 h-4 text-emerald-700 shrink-0" /> Akta Kelahiran</li>
                    <li class="flex items-center gap-2"><x-icon name="check_circle" class="w-4 h-4 text-emerald-700 shrink-0" /> Buku Nikah</li>
                    <li class="flex items-center gap-2"><x-icon name="check_circle" class="w-4 h-4 text-emerald-700 shrink-0" /> Pas Foto</li>
                    <li class="flex items-center gap-2"><x-icon name="check_circle" class="w-4 h-4 text-emerald-700 shrink-0" /> Surat Keterangan Sehat</li>
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <h3 class="text-xl font-bold text-[#0B3B24] font-serif flex items-center gap-2">
                <x-icon name="calendar_month" class="w-5 h-5 text-[#B78616]" />
                <span>Jadwal Keberangkatan</span>
            </h3>

            <div class="bg-white rounded-3xl shadow-sm border border-stone-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs font-serif">
                        <thead>
                            <tr class="bg-[#0B3B24] text-white">
                                <th class="p-4 font-semibold">Keberangkatan</th>
                                <th class="p-4 font-semibold">Maskapai</th>
                                <th class="p-4 font-semibold">Kuota</th>
                                <th class="p-4 font-semibold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 text-stone-700">
                            <tr>
                                <td class="p-4 font-medium">{{ $package['jadwal'] }}</td>
                                <td class="p-4">{{ $package['airline'] }}</td>
                                <td class="p-4 font-bold">45</td>
                                <td class="p-4 text-center">
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-3 py-1 rounded-full">Tersedia</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
@extends('layouts.supervisor')

@section('title', 'Profile Perusahaan - Supervisor ROZWIA')

@section('content')
<div class="space-y-6">

    <!-- header profil perusahaan -->
    <div class="bg-white border border-stone-200 rounded-[32px] p-6 sm:p-7 shadow-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-[800] font-['Abhaya_Libre'] text-stone-900">
                Profile Perusahaan
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 font-sans">
                Kelola Identitas &amp; Rekening — Rozwia Islamic Travel
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 text-sm font-sans font-semibold flex items-center gap-2 shadow-xs">
            <x-icon name="check_circle" class="w-5 h-5 text-emerald-600" />
            {{ session('success') }}
        </div>
    @endif

    <!-- konten utama -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- kolom kiri info perusahaan -->
        <div class="lg:col-span-5 space-y-6 relative">

            <!-- efek dekorasi latar -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
                <div class="w-72 h-72 bg-emerald-700/10 rounded-full blur-2xl"></div>
            </div>

            <!-- kartu identitas perusahaan -->
            <div class="relative z-10 text-center flex flex-col items-center space-y-4 py-12 px-6">
                <div class="flex items-center justify-center">
                    <img src="{{ asset('images/lOGO rozwia.png') }}" alt="Logo Rozwia" class="w-24 h-24 object-contain drop-shadow-md">
                </div>

                <div class="space-y-1">
                    <h2 class="text-3xl font-[800] font-['Abhaya_Libre'] text-stone-900">{{ $profile['nama_perusahaan'] ?? 'Rozwia Islamic Travel' }}</h2>
                    <p class="text-xs text-stone-600 font-sans">Penyelenggara Umroh dan Haji Resmi</p>
                </div>

                <div class="bg-white/90 backdrop-blur-xs border border-stone-200/80 text-stone-900 text-xs font-[700] px-5 py-2 rounded-full shadow-xs font-sans">
                    {{ $profile['no_izin'] ?? 'PPIU No. 1023/2021' }}
                </div>
            </div>

            <!-- kartu detail kontak -->
            <div class="bg-white rounded-[32px] p-6 sm:p-8 shadow-sm border border-stone-200 space-y-4 text-xs relative z-10 font-sans">

                <!-- input email -->
                <div class="flex items-start gap-3">
                    <x-icon name="mail" class="w-5 h-5 text-amber-600 mt-0.5" />
                    <div>
                        <span class="text-stone-400 block text-[10px] font-bold uppercase tracking-wider">Email</span>
                        <span class="font-bold text-[#0B3B24]">{{ $profile['email_perusahaan'] ?? 'rozwia@gmail.com' }}</span>
                    </div>
                </div>

                <!-- nomor whatsapp -->
                <div class="flex items-start gap-3 pt-3 border-t border-stone-100">
                    @include('partials.wa-icon', ['size' => '1.6em'])
                    <div>
                        <span class="text-stone-400 block text-[10px] font-bold uppercase tracking-wider">Telepon</span>
                        <span class="font-bold text-[#0B3B24]">{{ $profile['telepon_perusahaan'] ?? '0895-1882-9438' }}</span>
                    </div>
                </div>

                <!-- alamat kantor -->
                <div class="flex items-start gap-3 pt-3 border-t border-stone-100">
                    <x-icon name="location_on" class="w-5 h-5 text-rose-600 mt-0.5" />
                    <div>
                        <span class="text-stone-400 block text-[10px] font-bold uppercase tracking-wider">Alamat</span>
                        <p class="font-bold text-[#0B3B24] leading-relaxed mt-0.5">
                            {{ $profile['alamat_perusahaan'] ?? 'Kantor Pusat ROZWIA' }}
                        </p>
                    </div>
                </div>

            </div>

            <!-- kartu rekening perusahaan -->
            <div class="bg-gradient-to-br from-emerald-50 via-white to-amber-50/40 rounded-[32px] p-6 sm:p-8 shadow-sm border border-emerald-200 space-y-4 text-xs relative z-10 font-sans">
                <div class="flex items-center justify-between border-b border-emerald-200/70 pb-3">
                    <div class="flex items-center gap-2">
                        <x-icon name="account_balance" class="w-5 h-5 text-emerald-700" />
                        <h3 class="text-sm font-[800] font-['Abhaya_Libre'] text-[#0B3B24]">Rekening Perusahaan</h3>
                    </div>
                    <a href="{{ route('supervisor.rekening.index') }}" class="text-[11px] font-bold text-emerald-700 hover:underline">Kelola</a>
                </div>

                <div class="p-4 rounded-2xl bg-white border border-emerald-200/80 shadow-xs space-y-1">
                    <p class="font-bold text-emerald-900">{{ $profile['nama_bank'] ?? 'Bank Syariah Indonesia' }}</p>
                    <p class="text-sm font-bold text-[#0B3B24] font-mono">{{ $profile['nomor_rekening'] ?? '7123456789' }}</p>
                    <p class="text-[11px] text-emerald-700 font-medium">a.n {{ $profile['atas_nama'] ?? 'PT Pangrango Berkah Digital' }}</p>
                </div>
            </div>
        </div>

        <!-- form ubah profil & rekening -->
        <div class="lg:col-span-7 bg-white rounded-[32px] p-6 sm:p-8 shadow-sm border border-stone-200 space-y-6">

            <div class="space-y-1 border-b border-stone-100 pb-4">
                <h3 class="text-lg font-[800] font-['Abhaya_Libre'] text-stone-900">Identitas & Rekening Perusahaan</h3>
                <p class="text-xs text-stone-500 font-sans">Informasi ini tampil pada sidebar, laporan, dan dokumen jamaah.</p>
            </div>

            <form action="{{ route('supervisor.profile.update') }}" method="POST" class="space-y-4 text-xs font-sans">
                @csrf

                <!-- nama & nomor izin -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="font-[700] text-stone-800 block">Nama Perusahaan</label>
                        <input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan', $profile['nama_perusahaan'] ?? '') }}" required class="w-full px-4 py-3 rounded-2xl bg-white border border-stone-200 text-stone-800 font-medium hover:bg-[#DFD7C6]/30 focus:bg-[#DFD7C6]/40 focus:outline-none focus:border-stone-400 transition shadow-xs">
                    </div>
                    <div class="space-y-1.5">
                        <label class="font-[700] text-stone-800 block">Nomor Izin PPIU/PIHK</label>
                        <input type="text" name="no_izin" value="{{ old('no_izin', $profile['no_izin'] ?? '') }}" class="w-full px-4 py-3 rounded-2xl bg-white border border-stone-200 text-stone-800 font-medium hover:bg-[#DFD7C6]/30 focus:bg-[#DFD7C6]/40 focus:outline-none focus:border-stone-400 transition shadow-xs">
                    </div>
                </div>

                <!-- email & telepon -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="font-[700] text-stone-800 block">Email Perusahaan</label>
                        <input type="email" name="email_perusahaan" value="{{ old('email_perusahaan', $profile['email_perusahaan'] ?? '') }}" required class="w-full px-4 py-3 rounded-2xl bg-white border border-stone-200 text-stone-800 font-medium hover:bg-[#DFD7C6]/30 focus:bg-[#DFD7C6]/40 focus:outline-none focus:border-stone-400 transition shadow-xs">
                    </div>
                    <div class="space-y-1.5">
                        <label class="font-[700] text-stone-800 block">Telepon Perusahaan</label>
                        <input type="text" name="telepon_perusahaan" value="{{ old('telepon_perusahaan', $profile['telepon_perusahaan'] ?? '') }}" required class="w-full px-4 py-3 rounded-2xl bg-white border border-stone-200 text-stone-800 font-medium hover:bg-[#DFD7C6]/30 focus:bg-[#DFD7C6]/40 focus:outline-none focus:border-stone-400 transition shadow-xs">
                    </div>
                </div>

                <!-- alamat kantor -->
                <div class="space-y-1.5">
                    <label class="font-[700] text-stone-800 block">Alamat Perusahaan</label>
                    <textarea name="alamat_perusahaan" rows="3" required class="w-full px-4 py-3 rounded-2xl bg-white border border-stone-200 text-stone-800 font-medium hover:bg-[#DFD7C6]/30 focus:bg-[#DFD7C6]/40 focus:outline-none focus:border-stone-400 transition shadow-xs resize-none">{{ old('alamat_perusahaan', $profile['alamat_perusahaan'] ?? '') }}</textarea>
                </div>

                <!-- pemisah bagian rekening -->
                <div class="pt-4 mt-2 border-t border-emerald-200/70 space-y-4 rounded-2xl bg-emerald-50/60 p-4 border border-emerald-200">
                    <div class="space-y-1">
                        <h4 class="text-sm font-[800] font-['Abhaya_Libre'] text-[#0B3B24]">Informasi Rekening Bank</h4>
                        <p class="text-[11px] text-emerald-800">Nomor rekening resmi yang tampil pada invoice dan halaman pembayaran DP jamaah.</p>
                    </div>

                    <!-- bank & nomor rekening -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="font-[700] text-stone-800 block">Nama Bank</label>
                            <input type="text" name="nama_bank" value="{{ old('nama_bank', $profile['nama_bank'] ?? '') }}" required placeholder="Contoh: Bank Syariah Indonesia" class="w-full px-4 py-3 rounded-2xl bg-white border border-emerald-200 text-stone-900 font-medium focus:outline-none focus:border-emerald-600 transition shadow-xs">
                        </div>
                        <div class="space-y-1.5">
                            <label class="font-[700] text-stone-800 block">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $profile['nomor_rekening'] ?? '') }}" required placeholder="Contoh: 7123456789" class="w-full px-4 py-3 rounded-2xl bg-white border border-emerald-200 text-stone-900 font-mono font-bold focus:outline-none focus:border-emerald-600 transition shadow-xs">
                        </div>
                    </div>

                    <!-- nama pemilik rekening -->
                    <div class="space-y-1.5">
                        <label class="font-[700] text-stone-800 block">Atas Nama Rekening</label>
                        <input type="text" name="atas_nama" value="{{ old('atas_nama', $profile['atas_nama'] ?? '') }}" required placeholder="Contoh: PT Pangrango Berkah Digital" class="w-full px-4 py-3 rounded-2xl bg-white border border-emerald-200 text-stone-900 font-medium focus:outline-none focus:border-emerald-600 transition shadow-xs">
                    </div>
                </div>

                <!-- tombol simpan -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                    <button type="reset" class="px-6 py-3 rounded-2xl border border-stone-300 bg-[#DFD7C6]/40 text-stone-700 font-[700] hover:bg-[#DFD7C6] transition cursor-pointer shadow-xs">
                        Reset
                    </button>
                    <button type="submit" class="px-7 py-3 rounded-2xl bg-[#0B3B24] hover:bg-emerald-900 text-white font-[700] transition cursor-pointer shadow-md">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

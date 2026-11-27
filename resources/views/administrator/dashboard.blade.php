@extends('layouts.administrator')

@section('title', 'Manajemen Akun - Super Admin ROZWIA')

@section('content')
@php
    $roleBadgeClasses = [
        'administrator' => 'bg-purple-100 text-purple-800 border-purple-200',
        'supervisor'    => 'bg-amber-100 text-amber-900 border-amber-200',
        'admin'         => 'bg-sky-100 text-sky-800 border-sky-200',
        'jamaah'        => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    ];
    $roleTitles = [
        'administrator' => 'Manajemen Akun Super Admin',
        'supervisor'    => 'Manajemen Akun Supervisor',
        'admin'         => 'Manajemen Akun Admin',
        'jamaah'        => 'Manajemen Akun Jamaah',
    ];
    $roleTableTitles = [
        'administrator' => 'Daftar Akun Super Admin',
        'supervisor'    => 'Daftar Akun Supervisor',
        'admin'         => 'Daftar Akun Admin',
        'jamaah'        => 'Daftar Akun Jamaah',
    ];
    $roleSubtitles = [
        'administrator' => 'Tambah akun baru serta kelola seluruh akun Super Admin.',
        'supervisor'    => 'Tambah akun baru serta kelola seluruh akun Supervisor.',
        'admin'         => 'Tambah akun baru serta kelola seluruh akun Admin.',
        'jamaah'        => 'Tambah akun baru serta kelola seluruh akun Jamaah.',
    ];
    $pageHeading = $roleTitles[$role ?? 'semua'] ?? 'Manajemen Akun';
    $pageSubtitle = $roleSubtitles[$role ?? 'semua'] ?? 'Tambah akun Admin/Staf baru serta kelola seluruh akun Super Admin, Supervisor, Admin, dan Jamaah.';
    $tableHeading = $roleTableTitles[$role ?? 'semua'] ?? 'Daftar Seluruh Pengguna';
@endphp

<div class="space-y-6"
     x-data="{
         createOpen: {{ old('_form_mode') === 'create' && $errors->any() ? 'true' : 'false' }}
     }">

    <!-- header halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/85 backdrop-blur-md border border-stone-200/80 rounded-[28px] p-6 shadow-xs">
        <div class="space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-800">Pusat Kendali Akses &amp; Identitas</span>
            <h1 class="text-2xl sm:text-3xl font-bold font-serif-display text-stone-900">{{ $pageHeading }}</h1>
            <p class="text-xs sm:text-sm text-stone-500">
                {{ $pageSubtitle }}
            </p>
        </div>

        <button type="button"
                @click="createOpen = true"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0B3B24] hover:bg-emerald-900 text-white px-5 py-3 text-xs font-bold shadow-md transition cursor-pointer shrink-0">
            <x-icon name="person_add" class="w-4 h-4" />
            <span>Tambah Akun Baru</span>
        </button>
    </div>

    <!-- pesan error validasi -->
    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 space-y-1">
            <strong class="block font-bold">Gagal memproses perubahan:</strong>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ringkasan jumlah akun per role -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-semibold text-stone-500 block">Total Akun</span>
            <p class="mt-1 text-2xl font-bold font-sans tabular-nums text-stone-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-semibold text-purple-700 block">Super Admin</span>
            <p class="mt-1 text-2xl font-bold font-sans tabular-nums text-purple-900">{{ $stats['administrator'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-semibold text-amber-800 block">Supervisor</span>
            <p class="mt-1 text-2xl font-bold font-sans tabular-nums text-amber-900">{{ $stats['supervisor'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-semibold text-sky-700 block">Admin</span>
            <p class="mt-1 text-2xl font-bold font-sans tabular-nums text-sky-900">{{ $stats['admin'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-700 block">Jamaah</span>
            <p class="mt-1 text-2xl font-bold font-sans tabular-nums text-emerald-900">{{ $stats['jamaah'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-semibold text-stone-500 block">Aktif / Nonaktif</span>
            <p class="mt-1 text-lg font-bold font-sans tabular-nums">
                <span class="text-emerald-700">{{ $stats['active'] }}</span>
                <span class="text-stone-400">/</span>
                <span class="text-rose-600">{{ $stats['inactive'] }}</span>
            </p>
        </div>
    </div>

    <!-- filter & tabel pengguna -->
    <section class="rounded-[28px] border border-stone-200 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-stone-200 px-6 py-5">
            <div>
                <h2 class="font-serif-display text-2xl font-bold text-[#0B3B24]">{{ $tableHeading }}</h2>
                <p class="text-xs text-stone-500 mt-0.5">Menampilkan {{ $users->total() }} akun terdaftar.</p>
            </div>

            <form method="GET" action="{{ route('administrator.dashboard') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="role" value="{{ $role }}">

                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama, email, telepon..."
                       class="w-full sm:w-52 rounded-xl border border-stone-300 px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">

                <select name="status" class="rounded-xl border border-stone-300 px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                    <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="rounded-xl bg-[#0B3B24] hover:bg-emerald-900 px-4 py-2 text-xs font-bold text-white cursor-pointer transition">
                    Terapkan
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-100 text-stone-600">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">Pengguna</th>
                        <th class="px-3 py-3.5 font-semibold">Kontak &amp; Email</th>
                        <th class="px-3 py-3.5 font-semibold">Role</th>
                        <th class="px-3 py-3.5 font-semibold">Status</th>
                        <th class="px-4 py-3.5 text-center font-semibold">Pengaturan Akun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($users as $item)
                    @php
                        $isSelf = $item->id === auth()->id();
                        $isLastActiveAdmin = $item->role === 'administrator' && $item->status_akun === 'active' && $activeAdminCount <= 1;
                        $isProtected = $isSelf || $isLastActiveAdmin;
                        $isAktif = $item->status_akun === 'active';
                    @endphp
                    <tr class="hover:bg-stone-50/80 transition">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-100 font-bold text-[#0B3B24]">
                                    {{ \Illuminate\Support\Str::of($item->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <p class="font-bold text-[#0B3B24] truncate">{{ $item->name }}</p>
                                        @if($isSelf)
                                            <span class="rounded-full bg-[#0B3B24] px-2 py-0.5 text-[9px] font-bold text-amber-200">Anda</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-stone-400 font-sans tabular-nums">
                                        Terdaftar {{ $item->created_at?->format('d/m/Y') ?? '-' }}
                                        @if($item->must_change_password)
                                            · <span class="text-amber-700 font-semibold">Wajib Ganti Password</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-4 text-stone-700">
                            <span class="block font-medium">{{ $item->email }}</span>
                            <span class="block text-[11px] text-stone-500 font-sans tabular-nums mt-0.5">{{ $item->nomor_telepon ?: '-' }}</span>
                        </td>

                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $roleBadgeClasses[$item->role] ?? 'bg-stone-100 text-stone-700 border-stone-200' }}">
                                {{ $item->roleLabel() }}
                            </span>
                        </td>

                        <td class="px-4 py-4">
                            @if($isAktif)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold text-rose-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Inactive
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <div class="inline-flex items-center justify-center gap-1 whitespace-nowrap">
                                <form action="{{ route('administrator.users.toggle', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            @disabled($isProtected && $isAktif)
                                            onclick="return confirm('{{ $isAktif ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ addslashes($item->name) }}?')"
                                            title="{{ $isProtected && $isAktif ? 'Akun sendiri / Super Admin terakhir tidak dapat dinonaktifkan' : ($isAktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun') }}"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border px-2.5 text-[11px] font-semibold transition {{ $isProtected && $isAktif ? 'opacity-45 cursor-not-allowed border-stone-200 bg-stone-50 text-stone-400' : ($isAktif ? 'cursor-pointer border-emerald-200 bg-emerald-50/70 text-emerald-800 hover:bg-emerald-100' : 'cursor-pointer border-stone-300 bg-white text-stone-600 hover:bg-stone-100') }}">
                                        <span>{{ $isAktif ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                    </button>
                                </form>

                                <form action="{{ route('administrator.users.reset-password', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Reset password akun {{ addslashes($item->name) }} ke password sementara acak baru dan kirimkan ke {{ addslashes($item->email) }}?')"
                                            title="Reset password acak baru &amp; kirim via email"
                                            class="inline-flex h-8 items-center gap-1 rounded-lg border border-amber-300/80 bg-amber-50/80 px-2.5 text-[11px] font-semibold text-amber-900 hover:bg-amber-100 transition cursor-pointer">
                                        <x-icon name="lock_reset" class="w-3.5 h-3.5 text-amber-700" />
                                        <span>Reset</span>
                                    </button>
                                </form>

                                <span class="mx-0.5 h-4 w-px bg-stone-200" aria-hidden="true"></span>

                                <form action="{{ route('administrator.users.destroy', $item->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('PERHATIAN: Yakin hapus permanen akun {{ addslashes($item->name) }} ({{ addslashes($item->roleLabel()) }}) beserta seluruh data terkaitnya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            @disabled($isProtected)
                                            title="{{ $isProtected ? 'Akun sendiri / Super Admin terakhir dilindungi dari penghapusan' : 'Hapus Akun Permanen' }}"
                                            aria-label="Hapus Akun {{ $item->name }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border transition {{ $isProtected ? 'opacity-40 cursor-not-allowed border-stone-200 bg-stone-50 text-stone-400' : 'cursor-pointer border-rose-200 bg-white text-rose-600 hover:bg-rose-50 hover:border-rose-300' }}">
                                        <x-icon name="delete" class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-stone-500">
                            Belum ada akun yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="border-t border-stone-200 px-6 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </section>

    <!-- modal tambah akun -->
    <div x-show="createOpen"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
         @click.self="createOpen = false">
        <div class="w-full max-w-lg rounded-[28px] bg-white p-6 sm:p-8 shadow-2xl border border-stone-200 space-y-5">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <div>
                    <h3 class="font-serif-display text-2xl font-bold text-[#0B3B24]">Tambah Akun Baru</h3>
                    <p class="text-[11px] text-stone-500">Password sementara acak yang kuat akan dibuat otomatis dan dikirim ke email pengguna.</p>
                </div>
                <button type="button" @click="createOpen = false" class="text-stone-400 hover:text-stone-700 text-2xl leading-none cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('administrator.users.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_form_mode" value="create">

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-700">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('_form_mode') === 'create' ? old('name') : '' }}" required minlength="3"
                           placeholder="Nama staf / admin / jamaah"
                           class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-stone-700">Email *</label>
                        <input type="email" name="email" value="{{ old('_form_mode') === 'create' ? old('email') : '' }}" required
                               placeholder="adminbaru@rozwia.com"
                               class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-stone-700">No. Telepon / WA</label>
                        <input type="text" name="nomor_telepon" value="{{ old('_form_mode') === 'create' ? old('nomor_telepon') : '' }}"
                               placeholder="081234567890"
                               class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-stone-700">Role Akun *</label>
                        <select name="role" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                            @foreach(\App\Models\User::ROLES as $r)
                                <option value="{{ $r }}" {{ (old('_form_mode') === 'create' ? old('role', 'admin') : 'admin') === $r ? 'selected' : '' }}>
                                    {{ \App\Models\User::ROLE_LABELS[$r] ?? ucfirst($r) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-stone-700">Status Awal *</label>
                        <select name="status_akun" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B24]">
                            <option value="active" selected>Active (Langsung Aktif)</option>
                            <option value="inactive">Inactive (Ditangguhkan)</option>
                        </select>
                    </div>
                </div>

                <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-[11px] text-amber-900 flex items-center gap-2">
                    <x-icon name="lock" class="w-4 h-4 text-amber-700 shrink-0" />
                    <span>Demi keamanan standar production, sistem mewajibkan pengguna baru mengganti password sementara pada login pertama.</span>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createOpen = false" class="rounded-xl border border-stone-300 px-4 py-2.5 text-xs font-semibold text-stone-600 hover:bg-stone-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-[#0B3B24] hover:bg-emerald-900 px-5 py-2.5 text-xs font-bold text-white shadow-sm cursor-pointer">
                        Buat Akun &amp; Kirim Kredensial
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

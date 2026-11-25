@extends('layouts.admin')

@section('title', 'Aktivasi Akun - Admin ROZWIA')

@section('content')
<div class="space-y-6">
    <section class="rounded-[30px] bg-gradient-to-r from-[#08432b] to-[#176647] p-7 sm:p-9 text-white shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-amber-200">Keamanan akses</p>
        <h1 class="mt-2 font-serif-display text-3xl font-bold sm:text-4xl">Kelola Status Akun Pengguna</h1>
        <p class="mt-2 max-w-2xl text-sm text-emerald-50">Kelola status akses akun jamaah. Admin berwenang mengaktifkan atau menonaktifkan akun jamaah secara langsung demi kelancaran operasional travel.</p>
    </section>

    <section class="grid gap-5 md:grid-cols-2">
        <div class="rounded-[24px] border border-stone-200 bg-white p-6 shadow-sm">
            <span class="inline-flex rounded-2xl bg-emerald-100 p-3 text-[#0B3B24]">
                <x-icon name="verified_user" class="w-6 h-6" />
            </span>
            <p class="mt-5 text-sm text-stone-500">Akun Aktif</p>
            <p class="font-serif-display text-4xl font-bold text-stone-900">{{ $akunAktif }} <span class="text-xl font-medium">Akun</span></p>
        </div>
        <div class="rounded-[24px] border border-stone-200 bg-white p-6 shadow-sm">
            <span class="inline-flex rounded-2xl bg-rose-100 p-3 text-rose-700">
                <x-icon name="person_off" class="w-6 h-6" />
            </span>
            <p class="mt-5 text-sm text-stone-500">Akun Inactive</p>
            <p class="font-serif-display text-4xl font-bold text-stone-900">{{ $akunNonaktif }} <span class="text-xl font-medium">Akun</span></p>
        </div>
    </section>

    <section class="rounded-[28px] border border-stone-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-stone-200 p-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-serif-display text-2xl font-bold text-[#0B3B24]">Daftar Akun Jamaah</h2>
                <p class="mt-1 text-xs text-stone-500">Menampilkan {{ $users->count() }} akun yang sesuai filter.</p>
            </div>
            <form method="GET" class="flex flex-wrap gap-2">
                <input name="q" value="{{ $query }}" placeholder="Cari nama, email, telepon" class="min-w-52 rounded-xl border border-stone-300 px-3 py-2 text-xs outline-none focus:border-[#0B3B24]">
                <select name="status" class="rounded-xl border border-stone-300 bg-white px-3 py-2 text-xs outline-none focus:border-[#0B3B24]">
                    <option value="semua" @selected($status === 'semua')>Semua Status</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                <button class="rounded-xl bg-[#0B3B24] px-4 py-2 text-xs font-bold text-white cursor-pointer">Terapkan</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-100 text-stone-600">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Nama Akun</th>
                        <th class="px-4 py-4 font-semibold">Paket</th>
                        <th class="px-4 py-4 font-semibold">Email</th>
                        <th class="px-4 py-4 font-semibold">No. Telepon</th>
                        <th class="px-4 py-4 font-semibold">Total Jamaah</th>
                        <th class="px-4 py-4 font-semibold">Status</th>
                        <th class="px-4 py-4 font-semibold">Login Terakhir</th>
                        <th class="px-6 py-4 text-center font-semibold">Pengaturan Akun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($users as $jamaahUser)
                    <tr class="hover:bg-stone-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-100 font-bold text-[#0B3B24]">{{ \Illuminate\Support\Str::of($jamaahUser->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}</span>
                                <p class="font-bold text-[#0B3B24]">{{ $jamaahUser->name }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-block rounded-lg bg-amber-50 border border-amber-200 px-2.5 py-1 text-[11px] font-semibold text-amber-900">
                                {{ $jamaahUser->paket_dipesan ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-stone-700">
                            <span class="block">{{ $jamaahUser->email }}</span>
                        </td>
                        <td class="px-4 py-4 text-stone-700">{{ $jamaahUser->nomor_telepon ?: '-' }}</td>
                        <td class="px-4 py-4 font-semibold text-stone-700">{{ $jamaahUser->total_jamaah_booking }} Orang</td>
                        <td class="px-4 py-4">
                            @if($jamaahUser->status_akun === 'active')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                                </span>
                            @elseif($jamaahUser->layak_aktivasi)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Siap Diaktivasi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold text-rose-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Belum DP Verified
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-[11px] text-stone-600 font-sans tabular-nums">
                            @if($jamaahUser->last_login_at)
                                <span class="block font-semibold text-stone-800">{{ $jamaahUser->last_login_at->timezone('Asia/Jakarta')->format('d/m/Y') }}</span>
                                <span class="text-[10px] text-stone-500">{{ $jamaahUser->last_login_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</span>
                            @else
                                <span class="text-stone-400 italic">Belum login</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $isAktif = $jamaahUser->status_akun === 'active';
                                $willActivate = ! $isAktif;
                                $isLockedByDp = $willActivate && ! $jamaahUser->layak_aktivasi;
                            @endphp
                            <div class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <form action="{{ route('admin.aktivasi.store', $jamaahUser->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status_akun" value="{{ $willActivate ? 'active' : 'inactive' }}">
                                    <input type="hidden" name="confirm_status_change" value="1">
                                    <button type="submit"
                                            @disabled($isLockedByDp)
                                            onclick="return confirm('{{ $willActivate ? 'Aktifkan' : 'Nonaktifkan' }} akun {{ addslashes($jamaahUser->name) }}?')"
                                            title="{{ $isLockedByDp ? 'Aktivasi dikunci hingga pembayaran DP diverifikasi (DP Verified)' : ($isAktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun') }}"
                                            class="inline-flex h-8 items-center gap-2 rounded-lg border px-2.5 text-[11px] font-semibold transition {{ $isLockedByDp ? 'cursor-not-allowed border-stone-200 bg-stone-100 text-stone-400 opacity-65' : ($isAktif ? 'cursor-pointer border-emerald-200 bg-emerald-50/70 text-emerald-800 hover:bg-emerald-100' : 'cursor-pointer border-stone-300 bg-white text-stone-600 hover:bg-stone-100') }}">
                                        @if($isLockedByDp)
                                            <x-icon name="lock" class="w-3.5 h-3.5 text-stone-400" />
                                        @else
                                            <span class="relative h-3.5 w-6 rounded-full transition-colors {{ $isAktif ? 'bg-emerald-500' : 'bg-stone-300' }}">
                                                <span class="absolute top-0.5 h-2.5 w-2.5 rounded-full bg-white shadow-xs transition-all {{ $isAktif ? 'right-0.5' : 'left-0.5' }}"></span>
                                            </span>
                                        @endif
                                        <span>{{ $isLockedByDp ? 'Butuh DP Verified' : ($isAktif ? 'Aktif' : 'Nonaktif') }}</span>
                                    </button>
                                </form>

                                <form action="{{ route('admin.jamaah.reset-password', $jamaahUser->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Reset password akun {{ addslashes($jamaahUser->name) }} ke password acak baru dan kirimkan ke email {{ addslashes($jamaahUser->email) }}?')"
                                            title="Reset ke password acak baru & kirim via email"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-amber-300/80 bg-amber-50/80 px-2.5 text-[11px] font-semibold text-amber-900 hover:bg-amber-100 transition cursor-pointer">
                                        <x-icon name="lock_reset" class="w-3.5 h-3.5 text-amber-700" />
                                        <span>Reset Password</span>
                                    </button>
                                </form>

                                <span class="mx-0.5 h-4 w-px bg-stone-200" aria-hidden="true"></span>

                                <form action="{{ route('admin.aktivasi.destroy', $jamaahUser->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus permanen akun {{ addslashes($jamaahUser->name) }} beserta seluruh data pemesanannya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Hapus Akun Permanen"
                                            aria-label="Hapus Akun {{ $jamaahUser->name }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-600 hover:bg-rose-50 hover:border-rose-300 transition cursor-pointer">
                                        <x-icon name="delete" class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-sm text-stone-500">Tidak ada akun yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const savedY = sessionStorage.getItem('admin_aktivasi_scroll');
    if (savedY !== null) {
        window.scrollTo({ top: parseInt(savedY, 10), behavior: 'instant' });
        sessionStorage.removeItem('admin_aktivasi_scroll');
    }
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            sessionStorage.setItem('admin_aktivasi_scroll', window.scrollY);
        });
    });
});
</script>
@endsection

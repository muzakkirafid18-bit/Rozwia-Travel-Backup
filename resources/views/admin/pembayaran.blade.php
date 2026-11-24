@extends('layouts.admin')

@section('title', 'Kelola Pembayaran - Admin ROZWIA')

@section('content')
<div class="space-y-6" x-data="{ openPayment: null }">
    <section class="rounded-[30px] bg-gradient-to-r from-[#08432b] to-[#176647] p-7 sm:p-9 text-white shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-amber-200">Administrasi Keuangan</p>
        <h1 class="mt-2 font-serif-display text-3xl font-bold sm:text-4xl">Kelola Pembayaran Pengguna</h1>
        <p class="mt-2 max-w-2xl text-sm text-emerald-50">Pantau tagihan per booking dan catat pembayaran DP, cicilan, maupun pelunasan yang sudah terkonfirmasi melalui rekening resmi.</p>
    </section>

    <section class="grid gap-5 md:grid-cols-2">
        <div class="rounded-[24px] border border-stone-200 bg-white p-6 shadow-sm">
            <span class="inline-flex rounded-2xl bg-emerald-100 p-3 text-[#0B3B24]">
                <x-icon name="groups" class="w-6 h-6" />
            </span>
            <p class="mt-5 text-sm text-stone-500">Total Akun Terdaftar</p>
            <p class="font-serif-display text-4xl font-bold text-stone-900">{{ $totalAkun }}</p>
        </div>
        <div class="rounded-[24px] border border-stone-200 bg-white p-6 shadow-sm">
            <span class="inline-flex rounded-2xl bg-amber-50 p-3 text-amber-800">
                <x-icon name="payments" class="w-6 h-6" />
            </span>
            <p class="mt-5 text-sm text-stone-500">Total Sisa Bayar</p>
            <p class="font-serif-display text-3xl font-bold text-stone-900">{{ \App\Helpers\RozwiaHelper::formatRupiah($totalSisaBayar) }}</p>
        </div>
    </section>

    <section class="rounded-[28px] border border-stone-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-stone-200 p-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-serif-display text-2xl font-bold text-[#0B3B24]">Daftar Tagihan Jamaah</h2>
                <p class="mt-1 text-xs text-stone-500">Menampilkan {{ $bookings->count() }} booking yang sesuai filter.</p>
            </div>
            <form method="GET" class="flex flex-wrap gap-2 font-sans">
                <input name="q" value="{{ $query }}" placeholder="Cari nama, email, kode booking" class="min-w-52 rounded-xl border border-stone-300 px-3 py-2 text-xs outline-none focus:border-[#0B3B24]">
                <select name="status" class="rounded-xl border border-stone-300 bg-white px-3 py-2 text-xs outline-none focus:border-[#0B3B24]">
                    <option value="semua" @selected($status === 'semua')>Semua Status</option>
                    <option value="belum_lunas" @selected($status === 'belum_lunas')>Belum Lunas</option>
                    <option value="lunas" @selected($status === 'lunas')>Sudah Lunas</option>
                </select>
                <button class="rounded-xl bg-[#0B3B24] px-4 py-2 text-xs font-bold text-white cursor-pointer">Terapkan</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[950px] w-full text-left text-xs">
                <thead class="bg-stone-100 text-stone-600">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Nama Akun</th>
                        <th class="px-4 py-4 font-semibold">Paket / Booking</th>
                        <th class="px-4 py-4 font-semibold">Total Harga</th>
                        <th class="px-4 py-4 font-semibold">Sisa Bayar</th>
                        <th class="px-4 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-stone-50">
                        <td class="px-6 py-4">
                            <p class="font-bold text-[#0B3B24]">{{ $booking->user?->name ?? 'Akun terhapus' }}</p>
                            <p class="mt-0.5 text-[11px] text-stone-500">{{ $booking->user?->email ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-medium text-stone-800">{{ $booking->jadwal?->paket?->nama_paket ?? 'Paket belum tersedia' }}</p>
                            <p class="mt-0.5 font-mono text-[10px] text-stone-500">{{ $booking->kode_booking }}</p>
                        </td>
                        <td class="px-4 py-4 font-semibold font-mono">{{ \App\Helpers\RozwiaHelper::formatRupiah($booking->total_harga) }}</td>
                        <td class="px-4 py-4 font-bold font-mono {{ $booking->sisa_bayar > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ \App\Helpers\RozwiaHelper::formatRupiah($booking->sisa_bayar) }}</td>
                        <td class="px-4 py-4">
                            @if($booking->sisa_bayar <= 0)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-800">Sudah Lunas</span>
                            @else
                                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold text-rose-700">Belum Lunas</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($booking->sisa_bayar > 0)
                                <button type="button" @click="openPayment = {{ $booking->id }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#0B3B24] px-3 py-1.5 text-[10px] font-bold text-white hover:bg-emerald-900 cursor-pointer">
                                    <x-icon name="add" class="w-3.5 h-3.5" /> Uang Masuk
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-[10px] font-bold text-emerald-800">
                                    <x-icon name="verified" class="w-3.5 h-3.5" /> Sudah Lunas
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr x-show="openPayment === {{ $booking->id }}" x-cloak class="bg-emerald-50/60">
                        <td colspan="6" class="px-6 py-5">
                            <form action="{{ route('admin.booking.confirm-payment', $booking->id) }}"
                                  method="POST"
                                  x-data="{
                                    amount: 0,
                                    displayAmount: '',
                                    sisaBayar: {{ (int) $booking->sisa_bayar }},
                                    onInput(e) {
                                        const digits = String(e.target.value || '').replace(/\D/g, '');
                                        this.amount = digits ? parseInt(digits, 10) : 0;
                                        this.displayAmount = this.amount > 0 ? this.amount.toLocaleString('id-ID') : '';
                                    },
                                    fillLunas() {
                                        this.amount = this.sisaBayar;
                                        this.displayAmount = this.sisaBayar > 0 ? this.sisaBayar.toLocaleString('id-ID') : '';
                                    }
                                  }"
                                  class="flex flex-wrap items-end gap-3">
                                @csrf
                                <input type="hidden" name="amount" :value="amount">
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold text-stone-700">Nominal yang benar-benar diterima</label>
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center rounded-xl border bg-white overflow-hidden focus-within:border-[#0B3B24]"
                                             :class="amount > sisaBayar ? 'border-rose-500 focus-within:border-rose-500' : 'border-stone-300'">
                                            <span class="px-3 py-2 bg-stone-100 border-r border-stone-200 text-xs font-bold text-[#0B3B24] select-none">Rp</span>
                                            <input type="text"
                                                   inputmode="numeric"
                                                   :value="displayAmount"
                                                   @input="onInput($event)"
                                                   required
                                                   class="w-48 px-3 py-2 text-sm font-semibold font-mono outline-none bg-transparent"
                                                   :class="amount > sisaBayar ? 'text-rose-700' : 'text-stone-900'"
                                                   placeholder="5.000.000">
                                        </div>
                                        <button type="button"
                                                @click="fillLunas()"
                                                class="rounded-xl border border-[#0B3B24]/30 bg-white px-3 py-2 text-[11px] font-bold text-[#0B3B24] hover:bg-emerald-100/60 transition cursor-pointer whitespace-nowrap">
                                            Isi Lunas
                                        </button>
                                    </div>
                                    <p x-show="amount > sisaBayar" x-cloak class="text-rose-600 text-[11px] font-bold mt-1 flex items-center gap-1">
                                        <x-icon name="warning" class="w-3.5 h-3.5 shrink-0" /> <span>Nominal melebihi sisa tagihan!</span>
                                    </p>
                                </div>
                                <p class="max-w-sm text-[11px] leading-relaxed text-stone-600">
                                    Sisa tagihan: <strong>{{ \App\Helpers\RozwiaHelper::formatRupiah($booking->sisa_bayar) }}</strong>. Sistem menolak nominal yang melebihi sisa tagihan.
                                </p>
                                <button type="submit"
                                        :disabled="amount > sisaBayar || !amount || amount < 1"
                                        class="rounded-xl px-4 py-2.5 text-xs font-bold transition"
                                        :class="amount > sisaBayar || !amount || amount < 1
                                            ? 'bg-stone-300 text-stone-500 cursor-not-allowed'
                                            : 'bg-amber-500 text-[#0B3B24] cursor-pointer hover:bg-amber-400'"
                                        onclick="return confirm('Pastikan nominal bank sudah cocok sebelum disimpan. Lanjutkan?')">
                                    Simpan Pembayaran
                                </button>
                                <button type="button" @click="openPayment = null" class="px-2 py-2 text-xs font-semibold text-stone-600 cursor-pointer">Batal</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-stone-500">Tidak ada booking yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

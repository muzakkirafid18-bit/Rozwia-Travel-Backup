@php
    $pelunasanKode = $booking?->kode_booking ?? 'default';
    $pelunasanSisa = (float) ($sisaTagihan ?? 0);
    $pelunasanSapaan = $sapaan ?? \App\Helpers\RozwiaHelper::userSalutation($user, $booking?->jamaahDetails?->first());
    $pelunasanWaText = urlencode("Assalamualaikum Admin ROZWIA,\n\nSaya {$pelunasanSapaan} {$user->name} (Kode Booking: {$pelunasanKode}).\nAlhamdulillah seluruh 5 berkas persyaratan saya sudah diverifikasi.\nSaya ingin melanjutkan ke tahap *Pelunasan* sebesar " . \App\Helpers\RozwiaHelper::formatRupiah($pelunasanSisa) . ".");
@endphp

@if(!empty($showPelunasanPopup) && $pelunasanSisa > 0)
<div x-data="{
        storageKey: 'rozwia_pelunasan_popup_{{ $pelunasanKode }}',
        openPelunasanModal: false,
        init() {
            try {
                if (!sessionStorage.getItem(this.storageKey)) {
                    this.openPelunasanModal = true;
                }
            } catch (e) {
                this.openPelunasanModal = true;
            }
        },
        dismiss() {
            this.openPelunasanModal = false;
            try {
                sessionStorage.setItem(this.storageKey, 'dismissed');
            } catch (e) {}
        }
     }"
     x-show="openPelunasanModal"
     x-transition.opacity
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-[95] flex items-center justify-center bg-black/65 backdrop-blur-xs p-4 font-sans">
    <div @click.away="dismiss()"
         class="w-full max-w-md rounded-[28px] bg-white p-6 sm:p-7 shadow-2xl border border-stone-200 space-y-5 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-[#063B25]">
            <x-icon name="verified" class="w-8 h-8 text-emerald-700" />
        </div>

        <div class="space-y-2">
            <span class="inline-block rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-800">
                Seluruh Berkas Terverifikasi
            </span>
            <h3 class="font-serif-display text-2xl font-bold text-[#063B25]">Lanjut ke Tahap Pelunasan</h3>
            <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                Alhamdulillah {{ $pelunasanSapaan }} <strong>{{ $user->name }}</strong>, seluruh 5 berkas persyaratan telah disetujui Admin. Silakan lanjutkan ke tahap pelunasan sebelum jatuh tempo agar proses Surat Kemenag &amp; Visa dapat segera diproses.
            </p>
        </div>

        <div class="rounded-2xl bg-[#FBF9F3] border border-amber-200 p-4 text-center space-y-1">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-800">Sisa Tagihan Pelunasan</span>
            <strong class="block font-mono text-xl font-extrabold text-[#063B25]">
                {{ \App\Helpers\RozwiaHelper::formatRupiah($pelunasanSisa) }}
            </strong>
        </div>

        <div class="flex flex-col gap-2.5 pt-1">
            <a href="{{ route('user.formulir.dp') }}"
               @click="dismiss()"
               class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#063B25] hover:bg-emerald-900 py-3 px-4 text-xs font-bold text-white shadow-md transition">
                <x-icon name="payments" class="w-4 h-4" />
                <span>Lihat Rekening &amp; Panduan Pelunasan</span>
            </a>
            <a href="https://wa.me/6285122978237?text={{ $pelunasanWaText }}"
               target="_blank"
               @click="dismiss()"
               class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] hover:bg-[#20ba5a] py-3 px-4 text-xs font-bold text-white shadow-xs transition">
                <x-icon name="send" class="w-4 h-4" />
                <span>Konfirmasi Pelunasan via WhatsApp</span>
            </a>
            <button type="button"
                    @click="dismiss()"
                    class="w-full rounded-xl bg-stone-100 hover:bg-stone-200 py-2.5 text-xs font-semibold text-stone-700 transition cursor-pointer">
                Nanti Saja, Tutup
            </button>
        </div>
    </div>
</div>
@endif

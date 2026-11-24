<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\RozwiaHelper;
use App\Http\Controllers\Controller;
use App\Mail\AccountCredentialsMail;
use App\Mail\DocumentStatusMail;
use App\Mail\KemenagVisaUpdatedMail;
use App\Mail\PaymentVerified;
use App\Models\Booking;
use App\Models\Dokumen;
use App\Models\EmailLog;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Models\Jadwal;
use App\Models\JamaahDetail;
use App\Models\Maskapai;
use App\Models\Notifikasi;
use App\Models\Paket;
use App\Models\RekeningPerusahaan;
use App\Models\RiwayatPembayaran;
use App\Models\User;
use App\Services\AsyncMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    // halaman dashboard admin
    public function index(): View
    {
        // hitung ringkasan data dari database
        $pendingDp = RiwayatPembayaran::where('status', 'Menunggu_Verifikasi')->count();
        $pendingDokumen = Dokumen::where('status', 'Menunggu_Verifikasi')->count();
        $berkasVerified = Dokumen::where('status', 'Diverifikasi')->count();
        $berkasRevisi = Dokumen::whereIn('status', ['Ditolak', 'Perlu_Diperbaiki'])->count();

        $jamaahAktif = Booking::where('status_booking', 'Dikonfirmasi')->count();
        $akunAktif = User::where('role', 'jamaah')->where('status_akun', 'active')->count();
        $akunNonAktif = User::where('role', 'jamaah')->where('status_akun', '!=', 'active')->count();
        $antreanAktivasiAkun = $akunNonAktif;
        $totalJamaah = JamaahDetail::count();

        // data grafik pendaftaran 6 bulan terakhir
        $startMonth = now()->startOfMonth()->subMonths(5);
        $recentBookings = Booking::with('jadwal.paket.jenisLayanan')
            ->where('created_at', '>=', $startMonth)
            ->get();

        $chartMonths = collect(range(5, 0))->map(function (int $offset) use ($recentBookings) {
            $month = now()->startOfMonth()->subMonths($offset);
            $monthBookings = $recentBookings->filter(
                fn ($b) => $b->created_at && $b->created_at->format('Y-m') === $month->format('Y-m')
            );

            $umrohCount = 0;
            $hajiCount = 0;
            foreach ($monthBookings as $b) {
                $paket = $b->jadwal?->paket;
                $label = strtolower(($paket?->kategori ?? '') . ' ' . ($paket?->jenisLayanan?->nama_layanan ?? '') . ' ' . ($paket?->nama_paket ?? ''));
                $qty = max(1, (int) ($b->jumlah_jamaah ?? 1));
                if (str_contains($label, 'haji')) {
                    $hajiCount += $qty;
                } else {
                    $umrohCount += $qty;
                }
            }

            return [
                'label' => $month->translatedFormat('M Y'),
                'umroh' => $umrohCount,
                'haji'  => $hajiCount,
            ];
        })->values();
        $chartMax = max(5, (int) $chartMonths->flatMap(fn ($m) => [$m['umroh'], $m['haji']])->max());

        // daftar pembayaran dp yang masih pending
        $pembayaranPending = RiwayatPembayaran::with([
            'invoice.booking.user',
            'invoice.booking.jadwal.paket',
            'invoice.booking.invoices.riwayatPembayarans',
        ])->where('status', 'Menunggu_Verifikasi')
          ->latest()
          ->get()
          ->filter(function ($payment) {
              $booking = $payment->invoice?->booking;
              if (!$booking) return true;
              $paidBefore = (float) $booking->invoices->flatMap(fn($inv) => $inv->riwayatPembayarans->where('status', 'Berhasil'))->sum('amount');
              return $paidBefore == 0;
          })
          ->values();

        // daftar berkas yang menunggu dicek
        $dokumenPending = Dokumen::with([
            'jamaahDetail.booking.user',
        ])->where('status', 'Menunggu_Verifikasi')
          ->latest()
          ->get();

        $antreanAkunDokumen = $dokumenPending
            ->map(fn ($d) => $d->jamaahDetail?->booking?->id_pengguna ?? ('detail_' . $d->jamaah_detail_id))
            ->unique()
            ->count();

        // daftar booking yang tagihannya belum lunas
        $bookingBelumLunas = Booking::with([
            'user',
            'jadwal.paket',
            'invoices.riwayatPembayarans',
        ])->whereNotIn('status_booking', ['Dikonfirmasi', 'Dibatalkan'])
          ->latest()
          ->get()
          ->filter(function ($b) {
              $paid = (float)$b->invoices->flatMap(fn($inv) => $inv->riwayatPembayarans->where('status', 'Berhasil'))->sum('amount');
              return $paid < (float)($b->total_harga ?? 0);
          });

        $bookingMenungguDp = Booking::with([
            'user',
            'jadwal.paket',
            'invoices',
        ])->where('status_booking', 'Menunggu_DP')
          ->latest()
          ->get();

        return view('admin.dashboard', compact(
            'pendingDp', 'pendingDokumen', 'berkasVerified', 'berkasRevisi',
            'jamaahAktif', 'akunAktif', 'akunNonAktif', 'antreanAktivasiAkun', 'totalJamaah',
            'chartMonths', 'chartMax',
            'pembayaranPending', 'dokumenPending', 'antreanAkunDokumen', 'bookingBelumLunas', 'bookingMenungguDp'
        ));
    }

    /**
     * Halaman Kelola Pembayaran sesuai desain Admin.
     * Setiap baris mewakili satu booking agar total dan sisa tagihan tidak
     * tercampur apabila satu akun pernah melakukan lebih dari satu booking.
     */
    public function pembayaran(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'semua');

        $bookings = Booking::with([
            'user',
            'jadwal.paket',
            'invoices.riwayatPembayarans',
        ])->latest()->get()->map(function (Booking $booking) {
            $dibayar = (float) $booking->invoices
                ->flatMap(fn (Invoice $invoice) => $invoice->riwayatPembayarans)
                ->where('status', 'Berhasil')
                ->sum('amount');

            $booking->setAttribute('total_dibayar', $dibayar);
            $booking->setAttribute('sisa_bayar', max(0, (float) $booking->total_harga - $dibayar));
            $booking->setAttribute('pembayaran_menunggu', $booking->invoices
                ->flatMap(fn (Invoice $invoice) => $invoice->riwayatPembayarans)
                ->where('status', 'Menunggu_Verifikasi')
                ->count());

            return $booking;
        });

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $bookings = $bookings->filter(function (Booking $booking) use ($needle) {
                return str_contains(mb_strtolower((string) $booking->kode_booking), $needle)
                    || str_contains(mb_strtolower((string) ($booking->user?->name ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($booking->user?->email ?? '')), $needle);
            });
        }

        if ($status === 'lunas') {
            $bookings = $bookings->filter(fn (Booking $booking) => (float) $booking->sisa_bayar <= 0);
        } elseif ($status === 'belum_lunas') {
            $bookings = $bookings->filter(fn (Booking $booking) => (float) $booking->sisa_bayar > 0);
        } elseif ($status === 'menunggu') {
            $bookings = $bookings->filter(fn (Booking $booking) => (int) $booking->pembayaran_menunggu > 0);
        }

        $pembayaranMenunggu = RiwayatPembayaran::with(['invoice.booking.user'])
            ->where('status', 'Menunggu_Verifikasi')
            ->latest()
            ->get();
        $totalSisaBayar = (float) $bookings->sum('sisa_bayar');
        $totalAkun = $bookings->pluck('id_pengguna')->unique()->count();

        return view('admin.pembayaran', compact(
            'bookings', 'pembayaranMenunggu', 'totalSisaBayar', 'totalAkun', 'query', 'status'
        ));
    }

    /**
     * Halaman aktivasi akun. Kelayakan dihitung dari pembayaran yang sudah
     * diverifikasi, bukan dari input browser atau sekadar status invoice.
     */
    public function aktivasiAkun(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'semua');

        $users = User::where('role', 'jamaah')
            ->with(['bookings.jadwal.paket', 'bookings.jamaahDetails', 'bookings.invoices.riwayatPembayarans'])
            ->latest()
            ->get()
            ->map(function (User $user) {
                $verifiedPayments = $user->bookings
                    ->flatMap(fn (Booking $booking) => $booking->invoices)
                    ->flatMap(fn (Invoice $invoice) => $invoice->riwayatPembayarans)
                    ->where('status', 'Berhasil');

                $activeBookings = $user->bookings->whereNotIn('status_booking', ['Menunggu_DP', 'Dibatalkan', 'Ditolak']);
                $countedBookings = $activeBookings->isNotEmpty()
                    ? $activeBookings
                    : collect([$user->bookings->sortByDesc('id')->first()])->filter();

                $latestBooking = $countedBookings->sortByDesc('id')->first() ?? $user->bookings->sortByDesc('id')->first();
                $user->setAttribute('paket_dipesan', $latestBooking?->jadwal?->paket?->nama_paket ?? '-');
                $user->setAttribute('latest_email_log', EmailLog::where('id_pengguna', $user->id)->latest('id')->first());
                $user->setAttribute('total_jamaah_booking', $countedBookings->sum(function (Booking $booking) {
                    return max($booking->jamaahDetails->count(), (int) $booking->jumlah_jamaah, 1);
                }));
                $user->setAttribute('layak_aktivasi', $verifiedPayments->isNotEmpty());

                return $user;
            });

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $users = $users->filter(fn (User $user) =>
                str_contains(mb_strtolower($user->name), $needle)
                || str_contains(mb_strtolower($user->email), $needle)
                || str_contains(mb_strtolower((string) $user->nomor_telepon), $needle)
            );
        }

        if ($status === 'active') {
            $users = $users->where('status_akun', 'active');
        } elseif ($status === 'inactive') {
            $users = $users->where('status_akun', '!=', 'active');
        }

        $akunAktif = User::where('role', 'jamaah')->where('status_akun', 'active')->count();
        $akunNonaktif = User::where('role', 'jamaah')->where('status_akun', '!=', 'active')->count();

        return view('admin.aktivasi-akun', compact(
            'users', 'akunAktif', 'akunNonaktif', 'query', 'status'
        ));
    }

    /**
     * Ubah status akun dari switch/tombol Admin.
     * Admin berwenang mengaktifkan atau menonaktifkan akun jamaah secara fleksibel.
     */
    public function activateAccount(Request $request, int $userId): RedirectResponse
    {
        $request->validate([
            'status_akun' => ['required', 'in:active,inactive'],
        ]);

        $user = User::where('role', 'jamaah')->findOrFail($userId);
        $targetStatus = $request->string('status_akun')->toString();

        if ($user->status_akun === $targetStatus) {
            return back()->with('info', "Status akun {$user->name} sudah " . ($targetStatus === 'active' ? 'aktif' : 'nonaktif') . ".");
        }

        if ($targetStatus === 'active') {
            $hasVerifiedDp = $user->bookings()
                ->whereHas('invoices.riwayatPembayarans', fn ($q) => $q->where('status', 'Berhasil'))
                ->exists();

            if (! $hasVerifiedDp) {
                return back()->with('error', "Akun {$user->name} tidak dapat diaktifkan sebelum pembayaran DP diverifikasi (DP Verified) terlebih dahulu.");
            }
        }

        $plainPassword = null;
        DB::transaction(function () use ($user, $targetStatus, &$plainPassword) {
            $updateData = ['status_akun' => $targetStatus];
            if ($targetStatus === 'active' && empty($user->password)) {
                $plainPassword = $this->generateRandomPassword();
                $updateData['password'] = Hash::make($plainPassword);
                $updateData['must_change_password'] = true;
            }
            $user->update($updateData);

            Notifikasi::create([
                'id_pengguna'       => $user->id,
                'title'             => $targetStatus === 'active' ? 'Akun Portal Jamaah Aktif ✅' : 'Akun Portal Jamaah Dinonaktifkan 🔒',
                'message'           => $targetStatus === 'active'
                    ? 'Akun Portal Jamaah ROZWIA Anda telah diaktifkan oleh Admin. Silakan login ke portal jamaah untuk melengkapi data & melihat jadwal keberangkatan.'
                    : 'Akses Portal Jamaah Anda sementara dinonaktifkan oleh Admin. Silakan hubungi tim ROZWIA bila memerlukan bantuan.',
                'notification_type' => 'Booking',
            ]);
        });

        if ($plainPassword) {
            AsyncMailer::sendMailable($user->email, new AccountCredentialsMail($user, $plainPassword, 'Aktivasi Akun Portal Jamaah'));
        }

        $statusLabel = $targetStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        $msg = "Akun {$user->name} berhasil {$statusLabel}." . ($plainPassword ? " Password sementara: {$plainPassword}" : "");
        return back()->with('success', $msg);
    }

    /**
     * Hapus akun jamaah beserta seluruh data booking turunannya.
     *
     * Ini sengaja meminta password Admin dan konfirmasi email target agar
     * penghapusan permanen tidak dapat terjadi hanya dari salah klik tombol.
     * Foreign key database menghapus detail jamaah, invoice, pembayaran, dan
     * dokumen; file yang tersimpan privat dibersihkan setelah commit sukses.
     */
    public function deleteJamaahAccount(Request $request, int $userId): RedirectResponse
    {
        $user = User::where('role', 'jamaah')->findOrFail($userId);

        $bookings = Booking::with([
            'jamaahDetails.dokumens',
            'invoices.riwayatPembayarans',
        ])->where('id_pengguna', $user->id)->get();

        $privateFiles = $bookings
            ->flatMap(fn (Booking $booking) => $booking->jamaahDetails)
            ->flatMap(fn (JamaahDetail $jamaah) => $jamaah->dokumens)
            ->pluck('file_path')
            ->merge(
                $bookings
                    ->flatMap(fn (Booking $booking) => $booking->invoices)
                    ->flatMap(fn (Invoice $invoice) => $invoice->riwayatPembayarans)
                    ->pluck('bukti_transfer')
            )
            ->filter(fn ($path) => filled($path) && $path !== 'via-whatsapp')
            ->unique()
            ->values()
            ->all();

        $name = $user->name;
        DB::transaction(function () use ($user, $privateFiles) {
            // hapus user beserta data booking terkait
            Booking::where('id_pengguna', $user->id)->delete();
            $user->delete();

            DB::afterCommit(function () use ($privateFiles) {
                Storage::disk('private')->delete($privateFiles);
            });
        });

        return redirect()
            ->route('admin.aktivasi')
            ->with('success', "Akun jamaah {$name} beserta seluruh data pemesanannya berhasil dihapus.");
    }

    // verifikasi pembayaran dp
    public function verifyPayment(Request $request, int $pembayaranId): RedirectResponse
    {
        $admin = auth()->user();

        $pembayaran = RiwayatPembayaran::with(['invoice.booking.user', 'invoice.booking.invoices.riwayatPembayarans'])->findOrFail($pembayaranId);

        // pastiin statusnya masih menunggu
        if ($pembayaran->status !== 'Menunggu_Verifikasi') {
            return back()->with('error', 'Pembayaran ini sudah diproses sebelumnya.');
        }

        $pembayaran->update([
            'status'      => 'Berhasil',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'admin_notes' => $request->input('admin_notes', 'Pembayaran diverifikasi oleh admin.'),
        ]);

        // update status invoice
        $pembayaran->invoice->update(['status' => 'Lunas']);

        $booking = $pembayaran->invoice->booking;
        $user    = $booking->user;

        // aktifin akun jamaah kalau belum aktif & kirim password sementara
        $plainPassword = null;
        if ($user->status_akun !== 'active') {
            $plainPassword = $this->generateRandomPassword();
            $user->update([
                'status_akun'          => 'active',
                'password'             => Hash::make($plainPassword),
                'must_change_password' => true,
            ]);
        }

        // hitung total bayar yang masuk & update status booking
        $booking->load(['invoices.riwayatPembayarans', 'jamaahDetails.dokumens']);
        $totalBiaya = (float)($booking->total_harga ?? 0);
        $totalPaid  = (float)$booking->invoices->flatMap(fn($inv) => $inv->riwayatPembayarans->where('status', 'Berhasil'))->sum('amount');
        $isLunas    = ($totalBiaya > 0 && $totalPaid >= $totalBiaya);

        $this->syncBookingWorkflow($booking);

        if ($isLunas) {
            $title = 'Pembayaran Lunas Diverifikasi ✅';
            $msg   = "Alhamdulillah! Pembayaran pelunasan paket Anda sebesar " . \App\Helpers\RozwiaHelper::formatRupiah($pembayaran->amount) . " telah diverifikasi. Tagihan paket Anda telah LUNAS sepenuhnya.";
        } else {
            $title = 'Pembayaran Berhasil Diverifikasi ✅';
            $msg   = "Pembayaran sebesar " . \App\Helpers\RozwiaHelper::formatRupiah($pembayaran->amount) . " (Booking: {$booking->kode_booking}) telah diverifikasi oleh admin.";
        }

        // simpan notif ke dashboard user
        Notifikasi::create([
            'id_pengguna'       => $user->id,
            'title'             => $title,
            'message'           => $msg,
            'notification_type' => 'Pembayaran',
        ]);

        // kirim email bukti verifikasi pembayaran
        $paymentType = $isLunas ? 'Lunas' : 'DP';
        $emailToken = $this->createEmailLog($user, 'payment_verified', "Pembayaran {$paymentType} Diverifikasi - {$booking->kode_booking}");
        AsyncMailer::sendMailable($user->email, new PaymentVerified($user, $booking, $plainPassword, $paymentType, $emailToken));

        $formattedAmount = \App\Helpers\RozwiaHelper::formatRupiah($pembayaran->amount);
        return back()->with('success', "Alhamdulillah! Pembayaran jamaah {$user->name} sebesar {$formattedAmount} berhasil diverifikasi.");
    }

    // konfirmasi pembayaran dp / pelunasan via wa admin
    public function confirmWaPayment(Request $request, int $bookingId): RedirectResponse
    {
        $request->validate([
            'amount'   => ['required', 'numeric', 'min:1'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $admin   = auth()->user();
        $booking = Booking::with(['user', 'invoices.riwayatPembayarans', 'jamaahDetails'])->findOrFail($bookingId);
        $amount  = (float) $request->input('amount');
        $alreadyPaid = (float) $booking->invoices
            ->flatMap(fn (Invoice $invoice) => $invoice->riwayatPembayarans)
            ->where('status', 'Berhasil')
            ->sum('amount');
        $totalBiaya = (float) ($booking->total_harga ?? 0);
        $remaining = max(0, $totalBiaya - $alreadyPaid);

        // cek ulang nominal di backend biar nggak melebihi sisa tagihan
        
        if ($remaining <= 0) {
            return back()->with('error', 'Booking ini sudah lunas. Tidak dapat mencatat pembayaran tambahan.');
        }
        if ($amount > $remaining) {
            return back()->with('error', 'Nominal pembayaran melebihi sisa tagihan booking (' . RozwiaHelper::formatRupiah($remaining) . ').');
        }

        $wasAlreadyDp = $alreadyPaid > 0;

        $invoice = $booking->invoices()->where('status', '!=', 'Lunas')->latest()->first();
        if (!$invoice) {
            $rekening = RekeningPerusahaan::where('status', 'active')
                ->orWhere('status', 'aktif')
                ->first() ?? RekeningPerusahaan::first();

            $invoice = Invoice::create([
                'id_booking'       => $booking->id,
                'id_rekening'      => $rekening->id,
                'invoice_number'   => RozwiaHelper::generateInvoiceNumber(),
                'amount'           => $remaining,
                'batas_pembayaran' => now()->addDays(7),
                'status'           => $amount >= $remaining ? 'Lunas' : 'Sebagian',
            ]);
        } else {
            $paidOnInvoice = (float) $invoice->riwayatPembayarans
                ->where('status', 'Berhasil')
                ->sum('amount');
            $newPaidOnInvoice = $paidOnInvoice + $amount;
            $invoice->update([
                'status' => $newPaidOnInvoice >= (float) $invoice->amount ? 'Lunas' : 'Sebagian',
            ]);
        }

        RiwayatPembayaran::create([
            'id_invoice'        => $invoice->id,
            'amount'            => $amount,
            'metode_pembayaran' => $wasAlreadyDp ? 'Pelunasan / Cicilan (Konfirmasi WA)' : 'Uang Muka DP (Konfirmasi WA)',
            'bukti_transfer'    => 'via-whatsapp',
            'status'            => 'Berhasil',
            'verified_by'       => $admin->id,
            'verified_at'       => now(),
            'admin_notes'       => $wasAlreadyDp ? 'Pelunasan/Cicilan dikonfirmasi Admin via WhatsApp.' : 'DP dikonfirmasi Admin via WhatsApp.',
        ]);

        $user = $booking->user;
        $plainPassword = null;
        if ($user->status_akun !== 'active') {
            // bikin password acak sementara buat akun yang baru aktif
            $plainPassword = $this->generateRandomPassword();
            $user->update([
                'status_akun'          => 'active',
                'password'             => Hash::make($plainPassword),
                'must_change_password' => true,
            ]);
        }

        $booking->load(['invoices.riwayatPembayarans', 'jamaahDetails.dokumens']);
        $totalPaid = $alreadyPaid + $amount;
        $sisaAkhir = max(0, $totalBiaya - $totalPaid);
        $isLunas = ($totalBiaya > 0 && $totalPaid >= $totalBiaya);

        $this->syncBookingWorkflow($booking);

        if ($isLunas) {
            $title = 'Pembayaran Lunas Dikonfirmasi ✅';
            $msg   = "Alhamdulillah! Pembayaran pelunasan sebesar " . RozwiaHelper::formatRupiah($amount) . " telah dikonfirmasi oleh Admin. Tagihan paket Anda kini LUNAS sepenuhnya.";
        } elseif ($wasAlreadyDp) {
            $title = 'Pembayaran Cicilan Dikonfirmasi ✅';
            $msg   = "Alhamdulillah! Pembayaran cicilan sebesar " . RozwiaHelper::formatRupiah($amount) . " (Booking: {$booking->kode_booking}) telah dikonfirmasi oleh Admin. Sisa tagihan Anda saat ini: " . RozwiaHelper::formatRupiah($sisaAkhir) . ".";
        } else {
            $title = 'Pembayaran DP Dikonfirmasi ✅';
            $msg   = "Alhamdulillah! Pembayaran DP sebesar " . RozwiaHelper::formatRupiah($amount) . " (Booking: {$booking->kode_booking}) telah dikonfirmasi oleh Admin. Akun Anda kini aktif, silakan login dan lengkapi dokumen.";
        }

        Notifikasi::create([
            'id_pengguna'       => $user->id,
            'title'             => $title,
            'message'           => $msg,
            'notification_type' => 'Pembayaran',
        ]);

        $paymentType = $isLunas ? 'Lunas' : ($wasAlreadyDp ? 'Cicilan' : 'DP');
        $emailToken = $this->createEmailLog($user, 'payment_verified', "Pembayaran {$paymentType} Diverifikasi - {$booking->kode_booking}");
        AsyncMailer::sendMailable($user->email, new PaymentVerified($user, $booking, $plainPassword, $paymentType, $emailToken));

        if (!$wasAlreadyDp) {
            $flashSuccess = "Alhamdulillah! Pembayaran DP untuk {$user->name} sebesar " . RozwiaHelper::formatRupiah($amount) . " berhasil dikonfirmasi. Kredensial akun dikirim ke {$user->email}" . ($plainPassword ? " (Password: {$plainPassword})" : "") . ".";
        } elseif ($isLunas) {
            $flashSuccess = "Alhamdulillah! Pembayaran Pelunasan untuk {$user->name} sebesar " . RozwiaHelper::formatRupiah($amount) . " berhasil dikonfirmasi. Tagihan LUNAS sepenuhnya.";
        } else {
            $flashSuccess = "Alhamdulillah! Pembayaran Cicilan untuk {$user->name} sebesar " . RozwiaHelper::formatRupiah($amount) . " berhasil dicatat. Sisa tagihan: " . RozwiaHelper::formatRupiah($sisaAkhir) . ".";
        }

        return back()->with('success', $flashSuccess);
    }

    // reset password jamaah & kirim email
    public function resetJamaahPassword(int $userId): RedirectResponse
    {
        $user = User::where('role', 'jamaah')->findOrFail($userId);

        $tempPassword = $this->generateRandomPassword();
        $user->update([
            'password'             => Hash::make($tempPassword),
            'must_change_password' => true,
            'password_changed_at'  => null,
            'remember_token'       => null,
        ]);

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        $emailToken = $this->createEmailLog($user, 'credentials', 'Reset Password Akun Portal Jamaah');
        AsyncMailer::sendMailable($user->email, new AccountCredentialsMail($user, $tempPassword, 'Reset Password Akun Portal Jamaah', $emailToken));

        return back()->with('success', "Password akun {$user->name} ({$user->email}) berhasil di-reset. Password baru: {$tempPassword} (Kredensial juga telah dikirim ke email jamaah).");
    }

    private function createEmailLog(User $user, string $type, string $subject): string
    {
        $token = Str::random(48);
        EmailLog::create([
            'id_pengguna' => $user->id,
            'email'       => $user->email,
            'type'        => $type,
            'subject'     => $subject,
            'token'       => $token,
        ]);

        return $token;
    }

    private function generateRandomPassword(): string
    {
        return Str::upper(Str::random(3)) . random_int(100, 999) . Str::lower(Str::random(4));
    }

    // tolak bukti pembayaran
    public function rejectPayment(Request $request, int $pembayaranId): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $admin      = auth()->user();
        $pembayaran = RiwayatPembayaran::with('invoice.booking.user')->findOrFail($pembayaranId);

        $pembayaran->update([
            'status'      => 'Ditolak',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $user = $pembayaran->invoice->booking->user;

        Notifikasi::create([
            'id_pengguna'       => $user->id,
            'title'             => 'Pembayaran DP Ditolak ❌',
            'message'           => "Bukti transfer DP Anda ditolak. Alasan: {$request->input('admin_notes')}. Silakan upload ulang bukti pembayaran yang benar.",
            'notification_type' => 'Pembayaran',
        ]);

        return back()->with('success', "Pembayaran jamaah {$user->name} ditolak dan notifikasi telah dikirim.");
    }

    // verifikasi / revisi dokumen jamaah
    public function verifyDokumen(Request $request, int $dokumenId): RedirectResponse
    {
        $admin   = auth()->user();
        $dokumen = Dokumen::with('jamaahDetail.booking.user')->findOrFail($dokumenId);

        $status = $request->input('status', 'Diverifikasi');
        if (!in_array($status, ['Diverifikasi', 'Perlu_Diperbaiki', 'Ditolak'])) {
            $status = 'Diverifikasi';
        }

        $adminNotes = $request->input('admin_notes');

        $dokumen->update([
            'status'      => $status,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        $booking = $dokumen->jamaahDetail->booking;
        $user = $booking->user;
        $namaJamaah = $dokumen->jamaahDetail?->nama_lengkap ?? $user->name;

        $this->syncBookingWorkflow($booking);

        $title = match($status) {
            'Diverifikasi'     => "Dokumen {$dokumen->tipe_dokumen} ({$namaJamaah}) Diverifikasi ✅",
            'Perlu_Diperbaiki' => "Dokumen {$dokumen->tipe_dokumen} ({$namaJamaah}) Perlu Diperbaiki ⚠️",
            'Ditolak'          => "Dokumen {$dokumen->tipe_dokumen} ({$namaJamaah}) Ditolak ❌",
        };

        $pesan = match($status) {
            'Diverifikasi'     => "Dokumen {$dokumen->tipe_dokumen} milik {$namaJamaah} telah disetujui dan diverifikasi oleh admin.",
            'Perlu_Diperbaiki' => "Dokumen {$dokumen->tipe_dokumen} milik {$namaJamaah} perlu perbaikan: " . ($adminNotes ?: 'Mohon periksa kembali berkas Anda.'),
            'Ditolak'          => "Dokumen {$dokumen->tipe_dokumen} milik {$namaJamaah} ditolak: " . ($adminNotes ?: 'Dokumen tidak valid/tidak terbaca.'),
        };

        Notifikasi::create([
            'id_pengguna'       => $user->id,
            'title'             => $title,
            'message'           => $pesan,
            'notification_type' => 'Dokumen',
        ]);

        if (in_array($status, ['Perlu_Diperbaiki', 'Ditolak'], true)) {
            AsyncMailer::sendMailable($user->email, new DocumentStatusMail($user, $booking, 'needs_revision', $dokumen->jamaahDetail, $dokumen->tipe_dokumen, $adminNotes));
        } elseif ($status === 'Diverifikasi') {
            $this->sendBookingAllDocsVerifiedEmail($booking);
        }

        return back()->with('success', "Status dokumen {$dokumen->tipe_dokumen} milik {$namaJamaah} berhasil diubah menjadi " . str_replace('_', ' ', $status) . ".");
    }

    /**
     * Verifikasi seluruh dokumen milik satu jamaah sekaligus setelah Admin mengeceknya.
     */
    public function verifyAllJamaahDokumen(int $jamaahId): RedirectResponse
    {
        $admin = auth()->user();
        $jamaah = JamaahDetail::with(['dokumens', 'booking.user'])->findOrFail($jamaahId);

        $hasRevision = $jamaah->dokumens->contains(
            fn ($doc) => in_array($doc->status, ['Perlu_Diperbaiki', 'Ditolak'], true)
        );
        if ($hasRevision) {
            return back()->with('error', "Verifikasi sekaligus tidak dapat dilakukan karena masih ada berkas milik {$jamaah->nama_lengkap} yang berstatus Minta Perbaikan / Ditolak.");
        }

        $pendingDocs = $jamaah->dokumens->where('status', '!=', 'Diverifikasi');
        if ($pendingDocs->isEmpty()) {
            return back()->with('info', "Seluruh dokumen milik {$jamaah->nama_lengkap} sudah berstatus Diverifikasi.");
        }

        foreach ($pendingDocs as $doc) {
            $doc->update([
                'status'      => 'Diverifikasi',
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'admin_notes' => null,
            ]);
        }

        $booking = $jamaah->booking;
        if ($booking) {
            $this->syncBookingWorkflow($booking);
            if ($booking->user) {
                Notifikasi::create([
                    'id_pengguna'       => $booking->user->id,
                    'title'             => "Semua Berkas ({$jamaah->nama_lengkap}) Diverifikasi ✅",
                    'message'           => "Alhamdulillah! Seluruh {$pendingDocs->count()} dokumen persyaratan milik {$jamaah->nama_lengkap} telah diperiksa dan disetujui oleh admin.",
                    'notification_type' => 'Dokumen',
                ]);
            }
            $this->sendBookingAllDocsVerifiedEmail($booking);
        }

        return back()->with('success', "Alhamdulillah! {$pendingDocs->count()} dokumen milik {$jamaah->nama_lengkap} berhasil diverifikasi sekaligus.");
    }

    private function sendBookingAllDocsVerifiedEmail(Booking $booking): void
    {
        $booking->load(['user', 'jamaahDetails.dokumens']);
        if (!$booking->user) {
            return;
        }

        $requiredTypes = ['KTP', 'Kartu Keluarga', 'Paspor', 'Pas Foto 4x6', 'Buku Nikah / Akta Lahir'];
        $targetCount   = max(1, (int) ($booking->jumlah_jamaah ?? 1));

        if ($booking->jamaahDetails->count() < $targetCount) {
            return;
        }

        $allMembersVerified = $booking->jamaahDetails->every(function (JamaahDetail $member) use ($requiredTypes) {
            $verified = $member->dokumens
                ->where('status', 'Diverifikasi')
                ->pluck('tipe_dokumen')
                ->all();

            return count(array_diff($requiredTypes, $verified)) === 0;
        });

        if ($allMembersVerified) {
            $user = $booking->user;
            AsyncMailer::sendMailable($user->email, new DocumentStatusMail($user, $booking, 'all_verified'));
        }
    }

    // tolak dokumen jamaah
    public function rejectDokumen(Request $request, int $dokumenId): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $admin   = auth()->user();
        $dokumen = Dokumen::with('jamaahDetail.booking.user')->findOrFail($dokumenId);

        $dokumen->update([
            'status'      => 'Ditolak',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $booking = $dokumen->jamaahDetail->booking;
        $user = $booking->user;
        $namaJamaah = $dokumen->jamaahDetail?->nama_lengkap ?? $user->name;

        $this->syncBookingWorkflow($booking);

        Notifikasi::create([
            'id_pengguna'       => $user->id,
            'title'             => "Dokumen {$dokumen->tipe_dokumen} ({$namaJamaah}) Ditolak ❌",
            'message'           => "Dokumen {$dokumen->tipe_dokumen} milik {$namaJamaah} ditolak. Alasan: {$request->input('admin_notes')}. Silakan upload ulang.",
            'notification_type' => 'Dokumen',
        ]);

        return back()->with('success', "Dokumen milik {$namaJamaah} ditolak dan notifikasi dikirim ke {$user->name}.");
    }

    // download bukti transfer dari private storage (khusus admin)
    public function downloadBukti(int $pembayaranId): StreamedResponse
    {
        $pembayaran = RiwayatPembayaran::findOrFail($pembayaranId);

        if (!Storage::disk('private')->exists($pembayaran->bukti_transfer)) {
            abort(404, 'Berkas bukti transfer tidak ditemukan.');
        }

        return Storage::disk('private')->download($pembayaran->bukti_transfer);
    }

    // tampilkan file dokumen jamaah
    public function downloadDokumen(int $dokumenId)
    {
        $dokumen = Dokumen::findOrFail($dokumenId);

        if (!Storage::disk('private')->exists($dokumen->file_path)) {
            abort(404, 'Berkas dokumen tidak ditemukan.');
        }

        return Storage::disk('private')->response($dokumen->file_path);
    }

    // halaman daftar verifikasi dokumen
    public function dokumen(Request $request): View
    {
        $allDokumens = Dokumen::with(['jamaahDetail.booking.user'])->latest()->get();

        $jamaahWithDokumens = JamaahDetail::with(['booking.user', 'booking.jadwal.paket', 'dokumens'])
            ->whereHas('dokumens')
            ->latest()
            ->get();

        $menungguCount = $allDokumens->where('status', 'Menunggu_Verifikasi')->count();
        $perluPerbaikanCount = $allDokumens->where('status', 'Perlu_Diperbaiki')->count();
        $ditolakCount = $allDokumens->where('status', 'Ditolak')->count();
        $diverifikasiCount = $allDokumens->where('status', 'Diverifikasi')->count();

        $menungguJamaah = $allDokumens->where('status', 'Menunggu_Verifikasi')->map(fn($d) => $d->jamaahDetail?->nama_lengkap ?? $d->jamaahDetail?->booking?->user?->name ?? 'Jamaah')->unique()->values();
        $perluPerbaikanJamaah = $allDokumens->where('status', 'Perlu_Diperbaiki')->map(fn($d) => $d->jamaahDetail?->nama_lengkap ?? $d->jamaahDetail?->booking?->user?->name ?? 'Jamaah')->unique()->values();
        $ditolakJamaah = $allDokumens->where('status', 'Ditolak')->map(fn($d) => $d->jamaahDetail?->nama_lengkap ?? $d->jamaahDetail?->booking?->user?->name ?? 'Jamaah')->unique()->values();
        $diverifikasiJamaah = $allDokumens->where('status', 'Diverifikasi')->map(fn($d) => $d->jamaahDetail?->nama_lengkap ?? $d->jamaahDetail?->booking?->user?->name ?? 'Jamaah')->unique()->values();

        return view('admin.dokumen', compact(
            'allDokumens', 'jamaahWithDokumens', 'menungguCount', 'perluPerbaikanCount', 'ditolakCount', 'diverifikasiCount',
            'menungguJamaah', 'perluPerbaikanJamaah', 'ditolakJamaah', 'diverifikasiJamaah'
        ));
    }

    // halaman status surat kemenag & visa
    public function kemenag(): View
    {
        $jamaahDetails = JamaahDetail::with([
            'booking.jadwal.paket',
            'booking.user',
            'booking.invoices.riwayatPembayarans',
            'dokumens',
        ])
            ->latest()
            ->get();

        $kemenagTerbit = $jamaahDetails->where('status_kemenag', 'Terbit')->count();
        $visaTerbit    = $jamaahDetails->where('status_visa', 'Terbit')->count();
        $perluTindakan = $jamaahDetails->filter(fn($j) => $j->status_kemenag !== 'Terbit' || $j->status_visa !== 'Terbit')->count();

        return view('admin.kemenag', compact(
            'jamaahDetails', 'kemenagTerbit', 'visaTerbit', 'perluTindakan'
        ));
    }

    public function updateKemenag(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status_kemenag'      => ['required', 'in:Belum Diajukan,Diproses,Ditolak,Terbit'],
            'status_visa'         => ['required', 'in:Belum Diajukan,Diproses,Ditolak,Terbit'],
            'nomor_visa'          => ['nullable', 'string', 'max:50'],
            'tanggal_terbit_visa' => ['nullable', 'date'],
        ]);

        $jamaah = JamaahDetail::with(['booking.user', 'booking.invoices.riwayatPembayarans'])->findOrFail($id);

        if (!$jamaah->booking || !RozwiaHelper::isBookingLunas($jamaah->booking)) {
            return back()->with('error', 'Proses diblokir: Pembayaran jamaah ini belum LUNAS. Selesaikan pelunasan di menu Kelola Pembayaran terlebih dahulu.');
        }

        $statusVisa = $request->input('status_visa');
        if ($statusVisa !== 'Terbit' && ($request->filled('nomor_visa') || $request->filled('tanggal_terbit_visa'))) {
            return back()
                ->withInput()
                ->withErrors(['nomor_visa' => 'Nomor Visa dan Tanggal Terbit hanya dapat diisi apabila Status Visa sudah Terbit.']);
        }

        $jamaah->update([
            'status_kemenag'      => $request->input('status_kemenag'),
            'status_visa'         => $statusVisa,
            'nomor_visa'          => $statusVisa === 'Terbit' ? $request->input('nomor_visa') : null,
            'tanggal_terbit_visa' => $statusVisa === 'Terbit' ? $request->input('tanggal_terbit_visa') : null,
        ]);

        if ($jamaah->booking?->user) {
            $user = $jamaah->booking->user;
            $visaExtra = '';
            if ($jamaah->status_visa === 'Terbit' && !empty($jamaah->nomor_visa)) {
                $visaExtra .= " | No. Visa: {$jamaah->nomor_visa}";
            }
            if ($jamaah->status_visa === 'Terbit' && !empty($jamaah->tanggal_terbit_visa)) {
                $visaExtra .= ' (Terbit: ' . $jamaah->tanggal_terbit_visa->format('d/m/Y') . ')';
            }

            Notifikasi::create([
                'id_pengguna'       => $user->id,
                'title'             => 'Update Status Kemenag & Visa 📋',
                'message'           => "Status {$jamaah->nama_lengkap} — Surat Kemenag: {$jamaah->status_kemenag}, Visa: {$jamaah->status_visa}{$visaExtra}.",
                'notification_type' => 'Dokumen',
            ]);

            AsyncMailer::sendMailable($user->email, new KemenagVisaUpdatedMail($user, $jamaah->booking, $jamaah));
        }

        return back()->with('success', "Status Surat Kemenag & Visa untuk {$jamaah->nama_lengkap} berhasil disimpan dan dikirim ke email jamaah!");
    }

    // halaman hotel & pesawat
    public function hotel(): View
    {
        $jadwals       = Jadwal::with(['paket.paketTipeKamars.hotel', 'maskapai'])->latest()->get();
        $maskapais     = Maskapai::all();
        $hotels        = Hotel::all();
        $jamaahDetails = JamaahDetail::with(['booking.user', 'booking.jadwal.paket.paketTipeKamars.hotel', 'booking.jadwal.maskapai'])->latest()->get();

        return view('admin.hotel', compact('jadwals', 'maskapais', 'hotels', 'jamaahDetails'));
    }

    public function updateJadwalHotel(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'id_maskapai'           => ['required', 'exists:maskapai,id'],
            'nomor_penerbangan'     => ['nullable', 'string', 'max:50'],
            'tanggal_keberangkatan' => ['required', 'date'],
        ]);

        $jadwal = Jadwal::with(['paket', 'bookings.user'])->findOrFail($id);
        $jadwal->update([
            'id_maskapai'           => $request->input('id_maskapai'),
            'nomor_penerbangan'     => $request->input('nomor_penerbangan'),
            'tanggal_keberangkatan' => $request->input('tanggal_keberangkatan'),
        ]);
        $jadwal->load('maskapai');

        $notifiedUsers = [];
        foreach ($jadwal->bookings as $b) {
            if (in_array($b->status_booking, ['Dibatalkan', 'Ditolak'], true)) {
                continue;
            }
            if ($b->user && !in_array($b->user->id, $notifiedUsers, true)) {
                Notifikasi::create([
                    'id_pengguna'       => $b->user->id,
                    'title'             => 'Pembaruan Jadwal & Maskapai Penerbangan ✈️',
                    'message'           => 'Jadwal paket ' . ($jadwal->paket?->nama_paket ?? 'Umroh/Haji')
                        . ' diperbarui: ' . ($jadwal->tanggal_keberangkatan?->format('d/m/Y') ?? '-')
                        . ' • Maskapai: ' . ($jadwal->maskapai?->nama_maskapai ?? '-')
                        . ($jadwal->nomor_penerbangan ? " ({$jadwal->nomor_penerbangan})" : '') . '.',
                    'notification_type' => 'Keberangkatan',
                ]);
                $notifiedUsers[] = $b->user->id;
            }
        }

        return back()->with('success', 'Data jadwal & maskapai penerbangan berhasil diperbarui dan notifikasi dikirim ke jamaah terkait.');
    }

    public function updateManifestJamaah(Request $request, int $id): RedirectResponse
    {
        return back()->with('success', "Data jamaah berhasil diperbarui.");
    }

    // halaman jadwal & kirim manifest
    public function manifest(Request $request): View
    {
        $pakets = Paket::with(['jadwals.maskapai'])->latest('id')->get();

        $selectedPaket = null;
        if ($request->filled('paket_id')) {
            $selectedPaket = $pakets->firstWhere('id', (int) $request->query('paket_id'));
        } elseif ($request->filled('jadwal_id')) {
            $jadwalMatch = Jadwal::find((int) $request->query('jadwal_id'));
            if ($jadwalMatch) {
                $selectedPaket = $pakets->firstWhere('id', $jadwalMatch->id_paket);
            }
        }
        $selectedPaket = $selectedPaket ?? $pakets->first();

        $selectedJadwal = $selectedPaket?->jadwals->sortByDesc('tanggal_keberangkatan')->first();

        $readiness = $selectedPaket
            ? $this->manifestReadinessByPaket($selectedPaket)
            : [
                'readyAccounts'        => collect(),
                'queuedAccounts'       => collect(),
                'readyJamaahCount'     => 0,
                'totalRegisteredCount' => 0,
                'canSend'              => false,
            ];

        return view('admin.manifest', compact('pakets', 'selectedPaket', 'selectedJadwal', 'readiness'));
    }

    public function sendManifest(Request $request): RedirectResponse
    {
        $request->validate([
            'paket_id'   => ['nullable', 'integer', 'exists:paket,id', 'required_without:jadwal_id'],
            'jadwal_id'  => ['nullable', 'integer', 'exists:jadwal,id', 'required_without:paket_id'],
            'is_test'    => ['nullable', 'boolean'],
            'subjek'     => ['nullable', 'string', 'max:255'],
            'pesan'      => ['nullable', 'string', 'max:5000'],
            'lampiran.*' => ['nullable', 'file', 'max:10240'],
        ]);

        if ($request->filled('paket_id')) {
            $paket = Paket::with(['jadwals.maskapai'])->findOrFail($request->integer('paket_id'));
            $jadwal = $paket->jadwals->sortByDesc('tanggal_keberangkatan')->first();
        } else {
            $jadwal = Jadwal::with(['paket.jadwals.maskapai', 'maskapai'])->findOrFail($request->integer('jadwal_id'));
            $paket = $jadwal->paket;
        }

        $readiness = $this->manifestReadinessByPaket($paket);
        $isTest = $request->boolean('is_test');

        $paketNama = $paket->nama_paket ?? 'Umroh/Haji ROZWIA';
        $tglBerangkat = $jadwal?->tanggal_keberangkatan?->format('d/m/Y') ?? 'Menunggu jadwal';
        $maskapaiNama = $jadwal?->maskapai?->nama_maskapai ?? '-';

        // kalau cuma test kirim email ke admin sendiri
        if ($isTest) {
            $adminUser = auth()->user();
            $adminEmail = $adminUser->email ?? 'admin@rozwia.com';
            $subject = $request->filled('subjek')
                ? $request->input('subjek')
                : "[TESTING] Jadwal & Manifest Keberangkatan - {$paketNama} (ROZWIA)";

            $testSapaan = RozwiaHelper::userSalutation($adminUser);
            $defaultBody = $request->filled('pesan')
                ? str_replace(['[sapaan]', 'Bapak/Ibu', '[nama lengkap jamaah]'], [$testSapaan, $testSapaan, $adminUser->name ?? 'Admin'], $request->input('pesan'))
                : "Assalamu'alaikum {$testSapaan} " . ($adminUser->name ?? 'Admin') . ",\n\nIni adalah email uji coba (test) pengiriman jadwal dan manifest untuk paket {$paketNama} (Berangkat: {$tglBerangkat}, Maskapai: {$maskapaiNama}).\n\nSistem pengiriman email manifest berfungsi dengan baik.\n\nSalam hangat,\nTim ROZWIA Islamic Travel";

            $attachmentsPayload = [];
            if ($request->hasFile('lampiran')) {
                foreach ($request->file('lampiran') as $file) {
                    if ($file && $file->isValid()) {
                        $attachmentsPayload[] = [
                            'path' => $file->getRealPath(),
                            'as'   => $file->getClientOriginalName(),
                            'mime' => $file->getClientMimeType(),
                        ];
                    }
                }
            }

            AsyncMailer::sendRaw($adminEmail, $subject, $defaultBody, $adminUser->name ?? 'Admin', $attachmentsPayload);

            return back()->with('success', "Email uji coba manifest berhasil dikirim ke alamat Anda ({$adminEmail}).");
        }

        // pastikan minimal ada 1 akun yang kemenag & visanya sudah terbit
        if (! $readiness['canSend']) {
            return back()->with('error', "Belum ada akun jamaah pada paket {$paketNama} yang memenuhi syarat (Surat Kemenag Terbit & Visa Terbit).");
        }

        $kirimKeSemuaAnggota = $request->boolean('kirim_ke_semua_anggota');
        $sentAccounts = 0;
        $sentJamaahs = 0;
        $sentEmails = 0;

        foreach ($readiness['readyAccounts'] as $entry) {
            /** @var User $user */
            $user = $entry['user'];
            $group = $entry['jamaahs'];
            $first = $group->first();

            $ringkasanAnggota = $group->map(fn ($j) => $j->nama_lengkap)->implode(', ');
            $pesanNotif = "Alhamdulillah! Jadwal & manifest {$paketNama} (Berangkat: {$tglBerangkat}, Maskapai: {$maskapaiNama}) telah diterbitkan. Rincian jamaah: {$ringkasanAnggota}.";

            Notifikasi::create([
                'id_pengguna'       => $user->id,
                'title'             => 'Jadwal & Manifest Keberangkatan Terbit',
                'message'           => $pesanNotif,
                'notification_type' => 'Keberangkatan',
            ]);

            $customSubject = $request->filled('subjek')
                ? $request->input('subjek')
                : "Jadwal & Manifest Keberangkatan - {$paketNama} (ROZWIA)";

            $recipients = collect([$user->email]);

            if ($kirimKeSemuaAnggota) {
                $anggotaEmails = $group->map(fn ($j) => trim($j->email ?: ''))
                    ->filter(fn ($email) => $email && filter_var($email, FILTER_VALIDATE_EMAIL))
                    ->unique()
                    ->values();
                $recipients = $recipients->merge($anggotaEmails)->unique()->values();
            }

            $manifestAttachments = [];
            if ($request->hasFile('lampiran')) {
                foreach ($request->file('lampiran') as $file) {
                    if ($file && $file->isValid()) {
                        $manifestAttachments[] = [
                            'path' => $file->getRealPath(),
                            'as'   => $file->getClientOriginalName(),
                            'mime' => $file->getClientMimeType(),
                        ];
                    }
                }
            }

            foreach ($recipients as $recipientEmail) {
                $anggotaMatch = $group->first(fn ($j) => trim((string) $j->email) === $recipientEmail);
                $recipientName = $anggotaMatch?->nama_lengkap ?? $user->name;
                $sapaan = RozwiaHelper::userSalutation($user, $anggotaMatch ?? $first);

                $customBody = $request->filled('pesan')
                    ? str_replace(['[sapaan]', 'Bapak/Ibu', '[nama lengkap jamaah]'], [$sapaan, $sapaan, $recipientName], $request->input('pesan'))
                    : "Assalamu'alaikum {$sapaan} {$recipientName},\n\n{$pesanNotif}\n\nSilakan login ke Portal Jamaah ROZWIA untuk melihat detail lengkap di menu Status Perjalanan.\n\nSalam hangat,\nTim ROZWIA Islamic Travel";

                AsyncMailer::sendRaw($recipientEmail, $customSubject, $customBody, $recipientName, $manifestAttachments);
                $sentEmails++;
            }

            $sentAccounts++;
            $sentJamaahs += $entry['count'];
        }

        return back()->with('success', "Jadwal & manifest paket {$paketNama} berhasil dikirim ke {$sentAccounts} akun ({$sentJamaahs} jamaah, {$sentEmails} email terkirim).");
    }

    /**
     * Kesiapan pengiriman Jadwal & Manifest berdasarkan Paket:
     * Akun dinyatakan siap kirim jika seluruh anggota jamaahnya pada paket ini
     * telah memiliki Surat Kemenag 'Terbit' dan Visa 'Terbit'.
     * Akun yang belum memenuhi masuk ke 'queuedAccounts' (Akun Dalam Antrian).
     */
    private function manifestReadinessByPaket(Paket $paket): array
    {
        $jadwalIds = $paket->jadwals()->pluck('id');

        $bookings = Booking::with(['user', 'jamaahDetails'])
            ->whereIn('id_jadwal', $jadwalIds)
            ->whereNotIn('status_booking', ['Dibatalkan', 'Ditolak'])
            ->orderByDesc('id')
            ->get();

        $readyAccounts = collect();
        $queuedAccounts = collect();

        foreach ($bookings->groupBy('id_pengguna') as $uid => $userBookings) {
            if (empty($uid)) {
                continue;
            }

            $user = $userBookings->first()->user;
            if (! $user) {
                continue;
            }

            $jamaahs = $userBookings->flatMap->jamaahDetails->values();
            $totalOrang = max($jamaahs->count(), (int) $userBookings->sum('jumlah_jamaah'), 1);

            $hasKemenagAndVisa = $jamaahs->isNotEmpty()
                && $jamaahs->every(fn (JamaahDetail $j) => $j->status_kemenag === 'Terbit' && $j->status_visa === 'Terbit');

            $entry = [
                'user'    => $user,
                'jamaahs' => $jamaahs,
                'count'   => $totalOrang,
                'status'  => $hasKemenagAndVisa ? 'Siap Kirim' : 'Belum Memenuhi Syarat',
            ];

            if ($hasKemenagAndVisa) {
                $readyAccounts->push($entry);
            } else {
                $queuedAccounts->push($entry);
            }
        }

        return [
            'readyAccounts'        => $readyAccounts->values(),
            'queuedAccounts'       => $queuedAccounts->values(),
            'readyJamaahCount'     => (int) $readyAccounts->sum('count'),
            'totalRegisteredCount' => (int) ($readyAccounts->sum('count') + $queuedAccounts->sum('count')),
            'canSend'              => $readyAccounts->isNotEmpty(),
        ];
    }

    /**
     * Sinkronisasi otomatis status_booking & tracking_status seluruh anggota rombongan.
     */
    private function syncBookingWorkflow(Booking $booking): void
    {
        if (in_array($booking->status_booking, ['Dibatalkan', 'Ditolak', 'Selesai'], true)) {
            return;
        }

        $booking->load(['invoices.riwayatPembayarans', 'jamaahDetails.dokumens']);

        $totalBiaya = (float) ($booking->total_harga ?? 0);
        $totalPaid  = (float) $booking->invoices
            ->flatMap(fn ($inv) => $inv->riwayatPembayarans->where('status', 'Berhasil'))
            ->sum('amount');

        $hasVerifiedDp = $totalPaid > 0;
        $isLunas = $totalBiaya > 0 && $totalPaid >= $totalBiaya;

        $jamaahCount = max(1, $booking->jamaahDetails->count(), (int) ($booking->jumlah_jamaah ?? 1));
        $requiredDocs = $jamaahCount * 5;
        $verifiedDocs = $booking->jamaahDetails
            ->flatMap(fn ($j) => $j->dokumens)
            ->where('status', 'Diverifikasi')
            ->count();
        $allDocsVerified = $verifiedDocs >= $requiredDocs;

        if ($isLunas) {
            $booking->update(['status_booking' => 'Dikonfirmasi']);
        } elseif ($hasVerifiedDp && $allDocsVerified) {
            $booking->update(['status_booking' => 'Menunggu_Pelunasan']);
        } elseif ($hasVerifiedDp) {
            $booking->update(['status_booking' => 'Menunggu_Pengumpulan_Dokumen']);
        }
    }
}

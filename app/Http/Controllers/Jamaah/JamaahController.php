<?php

namespace App\Http\Controllers\Jamaah;

use App\Helpers\RozwiaHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\UploadDpRequest;
use App\Http\Requests\UploadDokumenRequest;
use App\Mail\BookingConfirmation;
use App\Mail\DpReceived;
use App\Models\Booking;
use App\Models\DetailKamarBooking;
use App\Models\Dokumen;
use App\Models\Invoice;
use App\Models\Jadwal;
use App\Models\JamaahDetail;
use App\Models\Notifikasi;
use App\Models\Paket;
use App\Models\PaketTipeKamar;
use App\Models\RekeningPerusahaan;
use App\Models\RiwayatPembayaran;
use App\Models\User;
use App\Services\AsyncMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JamaahController extends Controller
{
    // simpan pendaftaran paket baru
    public function storeBooking(BookingRequest $request, int $paketId): RedirectResponse
    {
        $validated = $request->validated();
        $normalizedPhone = $this->normalizePhoneNumber($validated['nomor_telepon'] ?? null);
        if ($normalizedPhone === null || strlen($normalizedPhone) < 11) {
            return back()
                ->withInput()
                ->withErrors(['nomor_telepon' => 'Nomor WhatsApp wajib diisi minimal 8 digit angka setelah +62.']);
        }
        $validated['nomor_telepon'] = $normalizedPhone;

        // cegah email staff atau akun aktif orang lain dipakai daftar publik
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            if ($existingUser->role !== 'jamaah') {
                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Email ini terdaftar sebagai akun pengelola sistem dan tidak dapat digunakan untuk pendaftaran jamaah.']);
            }
            if ($existingUser->status_akun === 'active' && auth()->id() !== $existingUser->id) {
                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Email ini sudah terdaftar sebagai akun aktif. Silakan login terlebih dahulu sebelum membuat pesanan baru.']);
            }
        }

        $paket = Paket::with(['jenisLayanan', 'jadwals.maskapai'])->findOrFail($paketId);

        // ambil jadwal yang dipilih, atau fallback jadwal pertama
        if (!empty($validated['jadwal_id'])) {
            $jadwal = Jadwal::with('maskapai')->where('id_paket', $paket->id)->findOrFail($validated['jadwal_id']);
        } else {
            $jadwal = $paket->jadwals()->first();
            if (!$jadwal) {
                // bikin jadwal default kalau paket belum ada jadwalnya
                $jadwal = Jadwal::firstOrCreate(
                    ['id_paket' => $paket->id],
                    [
                        'id_maskapai' => 1,
                        'tanggal_keberangkatan' => now()->addMonths(2),
                        'tanggal_pulang' => now()->addMonths(2)->addDays(9),
                        'kuota' => 45,
                        'status' => 'Tersedia',
                    ]
                );
            }
        }

        $jumlahJamaah = (int) ($validated['jumlah_jamaah'] ?? 1);

        // cegah dobel booking kalau tombol submit kepencet dua kali
        if ($existingUser) {
            $recentDuplicate = Booking::where('id_pengguna', $existingUser->id)
                ->where('id_jadwal', $jadwal->id)
                ->where('status_booking', 'Menunggu_DP')
                ->where('created_at', '>=', now()->subMinutes(2))
                ->latest()
                ->first();

            if ($recentDuplicate) {
                $signedDpUrl = URL::temporarySignedRoute(
                    'booking.dp.form',
                    now()->addDays(7),
                    ['kode_booking' => $recentDuplicate->kode_booking]
                );

                return redirect()
                    ->to($signedDpUrl)
                    ->with('success', "Pendaftaran Anda sudah tercatat ({$recentDuplicate->kode_booking}). Silakan selesaikan pembayaran DP di bawah ini.");
            }
        }

        $selectedTipe = strtolower($validated['tipe_kamar'] ?? 'quad');
        $kamars = $paket->paketTipeKamars()->with(['tipeKamar', 'hotel'])->get();
        
        // cek harga tipe kamar yang dipilih
        $selectedKamars = $kamars->filter(fn($k) => strtolower($k->tipeKamar?->nama ?? '') === $selectedTipe);

        $baseHarga = (float) $paket->harga_dasar;
        if ($selectedKamars->isNotEmpty()) {
            $firstHarga = (float) $selectedKamars->first()->harga;
            // kalau harga kamar sudah harga all-in paket, langsung pakai itu
            // kalau cuma selisih biaya kamar, tambahin ke harga dasar
            if ($firstHarga >= $baseHarga) {
                $hargaPerJamaah = $firstHarga;
            } else {
                $hargaPerJamaah = $baseHarga + (float) $selectedKamars->sum('harga');
            }
        } else {
            $hargaTambahan = match ($selectedTipe) {
                'double' => 7000000,
                'triple' => 3000000,
                default  => 0,
            };
            $hargaPerJamaah = $baseHarga + $hargaTambahan;
        }

        $totalHarga = $hargaPerJamaah * $jumlahJamaah;
        $dpAmount = strtolower((string) $paket->kategori) === 'haji'
            ? (float) round($totalHarga * 0.20)
            : (float) round($totalHarga * 0.10);
        $alamat = $validated['alamat'] ?? 'Belum dilengkapi';

        // bungkus transaction biar datanya konsisten
        $result = DB::transaction(function () use ($validated, $jadwal, $paket, $jumlahJamaah, $totalHarga, $dpAmount, $alamat, $selectedTipe, $selectedKamars, $hargaPerJamaah) {

            // cari atau bikin akun jamaah baru
            // generate password sementara acak
            $defaultPassword = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(3))
                . random_int(100, 999)
                . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4));
            $user = User::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name'          => $validated['nama_lengkap'],
                    'nomor_telepon' => $validated['nomor_telepon'],
                    'role'          => 'jamaah',
                    'status_akun'   => 'inactive', // baru aktif kalau DP sudah diverifikasi admin
                    'password'      => Hash::make($defaultPassword),
                    'must_change_password' => true, // paksa ganti password pas login pertama
                ]
            );

            // simpan data booking
            $booking = Booking::create([
                'id_pengguna'    => $user->id,
                'id_jadwal'      => $jadwal->id,
                'kode_booking'   => RozwiaHelper::generateBookingCode(),
                'nama_rombongan' => $validated['nama_lengkap'],
                'jumlah_jamaah'  => $jumlahJamaah,
                'total_harga'    => $totalHarga,
                'status_booking' => 'Menunggu_DP',
                'catatan'        => 'Tipe Kamar: ' . ucfirst($selectedTipe) . ' (' . RozwiaHelper::formatRupiah($hargaPerJamaah) . '/jamaah)',
            ]);

            // simpan pilihan tipe kamar buat masing-masing hotel
            foreach ($selectedKamars as $kamar) {
                DetailKamarBooking::create([
                    'id_booking'            => $booking->id,
                    'id_paket_tipe_kamar'   => $kamar->id,
                ]);
            }

            // simpan data jamaah utama (pendaftar)
            JamaahDetail::create([
                'id_booking'      => $booking->id,
                'nama_lengkap'    => $validated['nama_lengkap'],
                'jenis_kelamin'   => $validated['jenis_kelamin'] ?? null,
                'email'           => $validated['email'],
                'nomor_telepon'   => $validated['nomor_telepon'],
                'alamat'          => $alamat,
            ]);

            // simpan slot anggota tambahan kalau daftar lebih dari 1 orang
            $anggotaList = $validated['anggota_jamaah'] ?? [];
            $anggotaGenderList = $validated['anggota_gender'] ?? [];
            for ($i = 2; $i <= $jumlahJamaah; $i++) {
                $namaAnggota = !empty($anggotaList[$i - 2]) ? trim($anggotaList[$i - 2]) : ('Anggota Jamaah ' . $i);
                $genderAnggota = $anggotaGenderList[$i - 2] ?? null;
                JamaahDetail::create([
                    'id_booking'      => $booking->id,
                    'nama_lengkap'    => $namaAnggota,
                    'jenis_kelamin'   => in_array($genderAnggota, ['Laki-laki', 'Perempuan'], true) ? $genderAnggota : null,
                ]);
            }

            // bikin invoice DP otomatis
            $rekening = RekeningPerusahaan::where('status', 'active')
                ->orWhere('status', 'aktif')
                ->first() ?? RekeningPerusahaan::first();

            $invoice  = Invoice::create([
                'id_booking'        => $booking->id,
                'id_rekening'       => $rekening->id,
                'invoice_number'    => RozwiaHelper::generateInvoiceNumber(),
                'amount'            => $dpAmount,
                'batas_pembayaran'  => now()->addDays(3),
                'status'            => 'Belum_Dibayar',
            ]);

            // simpan notif pendaftaran berhasil
            Notifikasi::create([
                'id_pengguna'       => $user->id,
                'title'             => 'Pendaftaran Berhasil',
                'message'           => "Kode booking Anda: {$booking->kode_booking}. Silakan lakukan pembayaran DP sebelum {$invoice->batas_pembayaran->format('d/m/Y, H:i')}.",
                'notification_type' => 'Booking',
            ]);

            return compact('user', 'booking', 'invoice', 'rekening', 'defaultPassword');
        });

        $signedDpUrl = URL::temporarySignedRoute(
            'booking.dp.form',
            now()->addDays(7),
            ['kode_booking' => $result['booking']->kode_booking]
        );

        return redirect()
            ->to($signedDpUrl)
            ->with('success', "Pendaftaran paket berhasil! Silakan transfer DP ke rekening resmi ROZWIA dan kirimkan bukti transfer via WhatsApp Admin di bawah ini.");
    }

    // halaman dashboard jamaah
    public function dashboard(): View
    {
        $user    = auth()->user();
        $booking = Booking::with([
            'jadwal.paket.jenisLayanan',
            'jadwal.maskapai',
            'invoices.riwayatPembayarans',
            'jamaahDetails.dokumens',
        ])->where('id_pengguna', $user->id)
          ->latest()
          ->first();

        // hitung total bayar & sisa tagihan
        $totalBiaya    = $booking?->total_harga ?? 0;
        $totalDibayar  = 0;
        $statusDp      = 'Belum Ada Pembayaran';

        if ($booking) {
            foreach ($booking->invoices as $inv) {
                foreach ($inv->riwayatPembayarans as $rp) {
                    if ($rp->status === 'Berhasil') {
                        $totalDibayar += $rp->amount;
                    }
                }
            }

            $latestPayment = $booking->invoices
                ->flatMap(fn($inv) => $inv->riwayatPembayarans)
                ->sortByDesc('created_at')
                ->first();

            if ($totalBiaya > 0 && $totalDibayar >= $totalBiaya) {
                $statusDp = 'Pembayaran Lunas';
            } elseif ($latestPayment) {
                $statusDp = match ($latestPayment->status) {
                    'Berhasil'  => 'DP Terverifikasi Admin',
                    'Ditolak'   => 'Ditolak - Upload Ulang',
                    default     => 'Menunggu Verifikasi Admin',
                };
            }
        }

        $sisaTagihan = $totalBiaya - $totalDibayar;

        // cek kalau masih ada pembayaran pending
        $pendingPayment = $booking?->invoices
            ->flatMap(fn($inv) => $inv->riwayatPembayarans)
            ->where('status', 'Menunggu_Verifikasi')
            ->last();

        // ambil data jamaah pertama buat ringkasan dokumen
        $jamaahDetail = $booking?->jamaahDetails?->first();
        $dokumens     = $jamaahDetail?->dokumens ?? collect();

        return view('user.dashboard', compact(
            'user', 'booking', 'totalBiaya', 'totalDibayar', 'sisaTagihan', 'statusDp', 'dokumens', 'pendingPayment'
        ));
    }

    // halaman pembayaran DP publik setelah daftar paket
    public function showPublicDpForm(string $kodeBooking): View
    {
        $booking = Booking::with(['jadwal.paket.jenisLayanan', 'invoices.riwayatPembayarans', 'user'])
            ->where('kode_booking', $kodeBooking)
            ->firstOrFail();

        $invoice = $booking->invoices()->where('status', '!=', 'Lunas')->latest()->first()
            ?? $booking->invoices()->latest()->first();

        $rekening = RekeningPerusahaan::where('status', 'active')->first() ?? RekeningPerusahaan::first();
        $user = $booking->user;

        $totalBiaya   = (float)($booking->total_harga ?? 0);
        $totalDibayar = (float)$booking->invoices->flatMap(fn($inv) => $inv->riwayatPembayarans->where('status', 'Berhasil'))->sum('amount');
        $sisaTagihan  = max(0, $totalBiaya - $totalDibayar);

        return view('user.formulir-dp', compact('user', 'booking', 'rekening', 'invoice', 'totalBiaya', 'totalDibayar', 'sisaTagihan'));
    }

    /**
     * Simpan Bukti DP dari Alur Publik
     */
    public function storePublicDp(UploadDpRequest $request, string $kodeBooking): RedirectResponse
    {
        $validated = $request->validated();
        if (!empty($validated['nomor_telepon'])) {
            $validated['nomor_telepon'] = $this->normalizePhoneNumber($validated['nomor_telepon']) ?? $validated['nomor_telepon'];
        }

        $booking = Booking::with(['invoices.riwayatPembayarans', 'user'])
            ->where('kode_booking', $kodeBooking)
            ->firstOrFail();

        $invoice = $booking->invoices()->where('status', '!=', 'Lunas')->latest()->first();

        if (!$invoice) {
            $totalBiaya = (float)($booking->total_harga ?? 0);
            $totalDibayar = (float)$booking->invoices->flatMap(fn($inv) => $inv->riwayatPembayarans->where('status', 'Berhasil'))->sum('amount');
            $sisaTagihan = $totalBiaya - $totalDibayar;

            if ($sisaTagihan > 0) {
                $rekening = RekeningPerusahaan::where('status', 'active')
                    ->orWhere('status', 'aktif')
                    ->first() ?? RekeningPerusahaan::first();

                $invoice = Invoice::create([
                    'id_booking'       => $booking->id,
                    'id_rekening'      => $rekening->id,
                    'invoice_number'   => RozwiaHelper::generateInvoiceNumber(),
                    'amount'           => min((float)$validated['amount'], (float)$sisaTagihan),
                    'batas_pembayaran' => now()->addDays(7),
                    'status'           => 'Belum_Dibayar',
                ]);
            } else {
                return back()->with('error', 'Tagihan untuk pemesanan ini sudah lunas sepenuhnya.');
            }
        }

        // simpan file ke folder private storage
        $filePath = $request->file('bukti_dp')->store('payments', 'private');

        // catat riwayat pembayaran
        RiwayatPembayaran::create([
            'id_invoice'        => $invoice->id,
            'amount'            => $validated['amount'],
            'metode_pembayaran' => 'Transfer Bank Manual',
            'bukti_transfer'    => $filePath,
            'status'            => 'Menunggu_Verifikasi',
        ]);

        // simpan notif ke dashboard user ke pengguna
        Notifikasi::create([
            'id_pengguna'       => $booking->id_pengguna,
            'title'             => 'Bukti Pembayaran Berhasil Diunggah 📤',
            'message'           => "Bukti transfer pembayaran untuk pesanan {$booking->kode_booking} telah masuk ke sistem. Menunggu verifikasi admin.",
            'notification_type' => 'Pembayaran',
        ]);

        // kirim email tanda terima
        AsyncMailer::sendMailable($booking->user->email, new DpReceived($booking->user, $booking, $invoice));

        if (auth()->check()) {
            return redirect()
                ->route('user.dashboard')
                ->with('success', "Alhamdulillah! Bukti pembayaran untuk booking {$booking->kode_booking} berhasil diunggah. Tim Admin akan memverifikasinya.");
        }

        return redirect()
            ->route('login')
            ->with('success', "Alhamdulillah! Bukti transfer DP untuk booking {$booking->kode_booking} berhasil diunggah. Tim Admin akan memverifikasi dalam 1×24 jam. Akun Anda akan aktif setelah diverifikasi.");
    }

    // halaman form pembayaran dp dari dashboard
    public function showDpForm(): View|RedirectResponse
    {
        $user    = auth()->user();
        $booking = Booking::with(['jadwal.paket', 'invoices.riwayatPembayarans'])
            ->where('id_pengguna', $user->id)
            ->latest()
            ->first();

        if (!$booking) {
            return redirect()->route('user.dashboard')->with('error', 'Anda belum memiliki pemesanan paket aktif.');
        }

        // ambil rekening bank aktif
        $rekening = RekeningPerusahaan::where('status', 'active')
            ->orWhere('status', 'aktif')
            ->first() ?? RekeningPerusahaan::first();

        // ambil invoice yang belum lunas atau yang paling baru
        $invoice = $booking->invoices()->where('status', '!=', 'Lunas')->latest()->first()
            ?? $booking->invoices()->latest()->first();

        $totalBiaya   = (float)($booking->total_harga ?? 0);
        $totalDibayar = (float)$booking->invoices->flatMap(fn($inv) => $inv->riwayatPembayarans->where('status', 'Berhasil'))->sum('amount');
        $sisaTagihan  = max(0, $totalBiaya - $totalDibayar);

        // ambil daftar paket aktif
        $pakets = Paket::with('jenisLayanan')->get();

        return view('user.formulir-dp', compact('user', 'booking', 'rekening', 'pakets', 'invoice', 'totalBiaya', 'totalDibayar', 'sisaTagihan'));
    }

    // upload bukti transfer DP ke private storage
    public function storeDp(UploadDpRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if (!empty($validated['nomor_telepon'])) {
            $validated['nomor_telepon'] = $this->normalizePhoneNumber($validated['nomor_telepon']) ?? $validated['nomor_telepon'];
        }
        $user      = auth()->user();

        $booking = Booking::with(['invoices.riwayatPembayarans', 'jadwal.paket'])
            ->where('id_pengguna', $user->id)
            ->latest()
            ->firstOrFail();

        // cari invoice yang belum lunas
        $invoice = $booking->invoices()->where('status', '!=', 'Lunas')->latest()->first();

        // kalau dp sudah beres tapi masih ada sisa tagihan, pakai invoice terakhir
        if (!$invoice) {
            $totalBiaya = (float)($booking->total_harga ?? 0);
            $totalDibayar = (float)$booking->invoices->flatMap(fn($inv) => $inv->riwayatPembayarans->where('status', 'Berhasil'))->sum('amount');
            $sisaTagihan = $totalBiaya - $totalDibayar;

            if ($sisaTagihan > 0) {
                $rekening = RekeningPerusahaan::where('status', 'active')
                    ->orWhere('status', 'aktif')
                    ->first() ?? RekeningPerusahaan::first();

                $invoice = Invoice::create([
                    'id_booking'       => $booking->id,
                    'id_rekening'      => $rekening->id,
                    'invoice_number'   => RozwiaHelper::generateInvoiceNumber(),
                    'amount'           => min((float)$validated['amount'], (float)$sisaTagihan),
                    'batas_pembayaran' => now()->addDays(7),
                    'status'           => 'Belum_Dibayar',
                ]);
            } else {
                return redirect()->route('user.dashboard')->with('error', 'Tagihan paket Anda sudah lunas sepenuhnya.');
            }
        }

        // simpan file ke folder private storage
        $filePath = $request->file('bukti_dp')->store('payments', 'private');

        // catat riwayat pembayaran
        RiwayatPembayaran::create([
            'id_invoice'        => $invoice->id,
            'amount'            => $validated['amount'],
            'metode_pembayaran' => 'Transfer Bank Manual',
            'bukti_transfer'    => $filePath,
            'status'            => 'Menunggu_Verifikasi', // status awal menunggu cek admin
        ]);

        // simpan notif ke dashboard user ke database
        Notifikasi::create([
            'id_pengguna'       => $user->id,
            'title'             => 'Bukti Pembayaran Terkirim 📤',
            'message'           => "Bukti transfer pembayaran Anda sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " (Invoice: {$invoice->invoice_number}) telah kami terima dan sedang dalam antrian verifikasi admin.",
            'notification_type' => 'Pembayaran',
        ]);

        // kirim email notif pembayaran
        AsyncMailer::sendMailable($user->email, new DpReceived($user, $booking, $invoice));

        return redirect()
            ->route('user.dashboard')
            ->with('success', 'Bukti pembayaran sebesar Rp ' . number_format($validated['amount'], 0, ',', '.') . ' berhasil diunggah! Tim admin akan segera memverifikasinya.');
    }

    // halaman upload dokumen jamaah
    public function showDokumenForm(Request $request): View
    {
        $user    = auth()->user();
        $booking = Booking::with(['jamaahDetails.dokumens', 'invoices.riwayatPembayarans'])
            ->where('id_pengguna', $user->id)
            ->latest()
            ->first();

        if ($booking) {
            $currentCount = $booking->jamaahDetails->count();
            $targetCount  = (int)($booking->jumlah_jamaah ?? 1);
            if ($currentCount < $targetCount) {
                for ($i = $currentCount + 1; $i <= $targetCount; $i++) {
                    JamaahDetail::create([
                        'id_booking'      => $booking->id,
                        'nama_lengkap'    => 'Anggota Jamaah ' . $i,
                    ]);
                }
                $booking->load('jamaahDetails.dokumens');
            }
        }

        $jamaahs = $booking?->jamaahDetails ?? collect();
        $selectedId = $request->query('jamaah_id');
        $jamaahDetail = $selectedId
            ? ($jamaahs->firstWhere('id', (int) $selectedId) ?? $jamaahs->first())
            : $jamaahs->first();

        $dokumens = $jamaahDetail?->dokumens ?? collect();

        $requiredTypes = ['KTP', 'Kartu Keluarga', 'Paspor', 'Pas Foto 4x6', 'Buku Nikah / Akta Lahir'];
        $verifiedMembersCount = $jamaahs->filter(function ($j) use ($requiredTypes) {
            $verified = $j->dokumens->where('status', 'Diverifikasi')->pluck('tipe_dokumen')->all();
            return count(array_diff($requiredTypes, $verified)) === 0;
        })->count();
        $allBookingDocsVerified = $jamaahs->isNotEmpty() && $verifiedMembersCount >= max(1, (int) ($booking?->jumlah_jamaah ?? 1));
        $totalBiaya = (float) ($booking?->total_harga ?? 0);
        $totalDibayar = $booking ? RozwiaHelper::bookingTotalPaid($booking) : 0;
        $sisaTagihan = max(0, $totalBiaya - $totalDibayar);
        $isLunas = $booking ? RozwiaHelper::isBookingLunas($booking) : false;

        return view('user.dokumen', compact(
            'user',
            'booking',
            'jamaahs',
            'jamaahDetail',
            'dokumens',
            'verifiedMembersCount',
            'allBookingDocsVerified',
            'sisaTagihan',
            'isLunas'
        ));
    }

    // simpan berkas dokumen jamaah ke private storage
    public function storeDokumen(UploadDokumenRequest $request): RedirectResponse
    {
        $user    = auth()->user();
        $booking = Booking::with('jamaahDetails')
            ->where('id_pengguna', $user->id)
            ->latest()
            ->firstOrFail();

        $requestedJamaahId = $request->input('jamaah_detail_id');
        $jamaahDetail = $requestedJamaahId
            ? $booking->jamaahDetails->firstWhere('id', (int) $requestedJamaahId)
            : $booking->jamaahDetails->first();

        if (!$jamaahDetail) {
            return back()->with('error', 'Data jamaah tidak ditemukan. Silakan selesaikan pendaftaran terlebih dahulu.');
        }

        $passportUpdated = false;
        if ($request->has('passport_number')) {
            $newPassport = trim((string) $request->input('passport_number')) ?: null;
            if ($newPassport !== $jamaahDetail->passport_number) {
                $jamaahDetail->update(['passport_number' => $newPassport]);
                $passportUpdated = true;
            }
        }

        $tipeDokumen = [
            'ktp'        => 'KTP',
            'kk'         => 'Kartu Keluarga',
            'paspor'     => 'Paspor',
            'pas_foto'   => 'Pas Foto 4x6',
            'buku_nikah' => 'Buku Nikah / Akta Lahir',
        ];

        $uploadedCount = 0;
        foreach ($tipeDokumen as $field => $label) {
            if ($request->hasFile($field)) {
                $uploadedCount++;
                // simpan dokumen ke private storage (kompres dulu kalau gambar)
                $path = $this->storeCompressedDocument($request->file($field));

                // simpan atau update data dokumen
                Dokumen::updateOrCreate(
                    [
                        'jamaah_detail_id' => $jamaahDetail->id,
                        'tipe_dokumen'     => $label,
                    ],
                    [
                        'file_path'   => $path,
                        'status'      => 'Menunggu_Verifikasi',
                        'admin_notes' => null,
                        'verified_by' => null,
                        'verified_at' => null,
                    ]
                );
            }
        }

        if ($uploadedCount === 0 && $passportUpdated) {
            return back()->with('success', "Nomor paspor milik {$jamaahDetail->nama_lengkap} berhasil disimpan.");
        }

        if ($uploadedCount === 0) {
            return redirect()
                ->route('user.dokumen', ['jamaah_id' => $jamaahDetail->id])
                ->with('error', 'Silakan pilih minimal 1 berkas terlebih dahulu sebelum menekan Simpan Berkas.');
        }

        Notifikasi::create([
            'id_pengguna'       => $user->id,
            'title'             => 'Dokumen Berhasil Diunggah',
            'message'           => "Dokumen persyaratan untuk jamaah {$jamaahDetail->nama_lengkap} telah diunggah dan sedang menunggu verifikasi admin.",
            'notification_type' => 'Dokumen',
        ]);

        $redirect = redirect()
            ->route('user.dokumen', ['jamaah_id' => $jamaahDetail->id])
            ->with('success', "Alhamdulillah! {$uploadedCount} dokumen milik {$jamaahDetail->nama_lengkap} berhasil diunggah dan menunggu verifikasi admin.");

        if ((int) ($booking->jumlah_jamaah ?? 1) > 1) {
            $redirect->with(
                'rombongan_popup',
                "Berkas milik {$jamaahDetail->nama_lengkap} telah tersimpan. Saat ini menunggu seluruh {$booking->jumlah_jamaah} jamaah yang Anda bawa selesai melengkapi berkas dan diverifikasi Admin terlebih dahulu sebelum email kelulusan berkas dikirim."
            );
        }

        return $redirect;
    }

    // halaman tracking status perjalanan
    public function statusTracking(): View
    {
        $user    = auth()->user();
        $booking = Booking::with([
            'jadwal.paket.rangkaianIbadahs',
            'jadwal.paket.paketTipeKamars.hotel',
            'jadwal.maskapai',
            'invoices.riwayatPembayarans',
            'jamaahDetails.dokumens',
        ])->where('id_pengguna', $user->id)
          ->latest()
          ->first();

        // hitung progres keberangkatan dari seluruh anggota
        $trackingLevel = 0;
        $trackingStatus = 'Belum Ada Booking';

        if ($booking) {
            $hasVerifiedDp = $booking->invoices
                ->flatMap(fn($inv) => $inv->riwayatPembayarans)
                ->where('status', 'Berhasil')
                ->isNotEmpty();

            $jamaahCount = max(1, $booking->jamaahDetails->count(), (int) ($booking->jumlah_jamaah ?? 1));
            $requiredDocs = $jamaahCount * 5;
            $allDokumens = $booking->jamaahDetails->flatMap(fn($j) => $j->dokumens);
            $verifiedDocs = $allDokumens->where('status', 'Diverifikasi')->count();
            $allDocsVerified = $verifiedDocs >= $requiredDocs;

            if ($hasVerifiedDp && $allDocsVerified) {
                $trackingLevel  = 3; // lanjut cek dokumen & pelunasan
                $trackingStatus = 'Dokumen Lengkap & Terverifikasi';
            } elseif ($hasVerifiedDp) {
                $trackingLevel  = 2;
                $trackingStatus = "DP Terverifikasi - Menunggu Verifikasi Dokumen ({$verifiedDocs}/{$requiredDocs})";
            } elseif ($booking->status_booking === 'Menunggu_DP' && $booking->invoices->flatMap(fn($inv) => $inv->riwayatPembayarans)->isNotEmpty()) {
                $trackingLevel  = 1;
                $trackingStatus = 'Menunggu Verifikasi DP oleh Admin';
            } else {
                $trackingLevel  = 0;
                $trackingStatus = 'Menunggu Pembayaran DP';
            }
        }

        $paketHotels = $booking?->jadwal?->paket?->paketTipeKamars
            ?->map(fn ($ptk) => $ptk->hotel)
            ->filter()
            ->unique('id')
            ->values() ?? collect();

        return view('user.status-tracking', compact('user', 'booking', 'trackingLevel', 'trackingStatus', 'paketHotels'));
    }

    /**
     * Profil Jamaah & Kelola Anggota Jamaah
     * Memastikan slot jamaah sesuai jumlah_jamaah pada booking.
     */
    public function profile(): View
    {
        $user = auth()->user();
        $booking = Booking::with([
            'jadwal.paket',
            'jamaahDetails.dokumens',
        ])->where('id_pengguna', $user->id)
            ->latest()
            ->first();

        if ($booking) {
            $currentCount = $booking->jamaahDetails->count();
            $targetCount  = (int)($booking->jumlah_jamaah ?? 1);

            // samain jumlah slot detail jamaah dengan jumlah orang di booking
            if ($currentCount < $targetCount) {
                for ($i = $currentCount + 1; $i <= $targetCount; $i++) {
                    JamaahDetail::create([
                        'id_booking'      => $booking->id,
                        'nama_lengkap'    => 'Anggota Jamaah ' . $i,
                    ]);
                }
                $booking->load('jamaahDetails.dokumens');
            }
        }

        $jamaahs = $booking?->jamaahDetails ?? collect();

        return view('user.profile', compact('user', 'booking', 'jamaahs'));
    }

    /**
     * Update / Lengkapi Data Anggota Jamaah
     */
    public function updateJamaahDetail(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $jamaah = JamaahDetail::whereHas('booking', function ($q) use ($user) {
            $q->where('id_pengguna', $user->id);
        })->findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap'    => ['required', 'string', 'max:255'],
            'nik'             => ['nullable', 'string', 'max:20'],
            'tempat_lahir'    => ['nullable', 'string', 'max:100'],
            'tanggal_lahir'   => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin'   => ['nullable', 'in:Laki-laki,Perempuan'],
            'email'           => ['nullable', 'email', 'max:255'],
            'nomor_telepon'   => ['required', 'string', 'max:25', 'regex:/^(?:\+?62|0)?[0-9\-\s\(\)]{8,18}$/'],
            'alamat'          => ['nullable', 'string', 'max:500'],
        ], [
            'nomor_telepon.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'nomor_telepon.regex'    => 'Format Nomor WhatsApp tidak valid (minimal 8 digit angka setelah +62).',
        ]);

        $normalizedPhone = $this->normalizePhoneNumber($validated['nomor_telepon']);
        if (!$normalizedPhone || strlen($normalizedPhone) < 11) {
            return back()->withInput()->withErrors(['nomor_telepon' => 'Nomor WhatsApp wajib diisi minimal 8 digit angka setelah +62.']);
        }
        $validated['nomor_telepon'] = $normalizedPhone;

        $jamaah->update($validated);

        $booking = $jamaah->booking()->with('jamaahDetails')->first();
        $members = $booking?->jamaahDetails ?? collect([$jamaah]);
        $targetCount = max(1, (int) ($booking?->jumlah_jamaah ?? 1));

        $incompleteCount = $members->filter(function (JamaahDetail $m) {
            return empty(trim((string) $m->nama_lengkap))
                || str_starts_with((string) $m->nama_lengkap, 'Anggota Jamaah ')
                || empty($m->nik)
                || empty($m->tempat_lahir)
                || empty($m->tanggal_lahir)
                || empty($m->jenis_kelamin)
                || empty($m->nomor_telepon);
        })->count();

        $allProfilesComplete = $members->count() >= $targetCount && $incompleteCount === 0;

        if ($targetCount <= 1 || $allProfilesComplete) {
            return redirect()
                ->route('user.dokumen', ['jamaah_id' => $jamaah->id])
                ->with('success', "Alhamdulillah! Data profil {$jamaah->nama_lengkap} tersimpan. Silakan lanjut mengisi Nomor Paspor dan mengunggah berkas dokumen.");
        }

        return back()->with('success', "Data profil {$jamaah->nama_lengkap} berhasil disimpan! Masih ada {$incompleteCount} anggota rombongan lagi yang perlu dilengkapi datanya sebelum pindah ke Dokumen.");
    }

    public function cetakKuitansi(): View|RedirectResponse
    {
        $user = auth()->user();
        $booking = Booking::with([
            'jadwal.paket.jenisLayanan',
            'jadwal.maskapai',
            'jamaahDetails',
            'invoices.riwayatPembayarans',
        ])->where('id_pengguna', $user->id)
          ->latest()
          ->first();

        if (!$booking) {
            return redirect()->route('user.dashboard')->with('error', 'Data pemesanan belum tersedia.');
        }

        $company = RozwiaHelper::companyProfile();
        $payments = $booking->invoices
            ->flatMap(fn ($inv) => $inv->riwayatPembayarans)
            ->where('status', 'Berhasil')
            ->sortBy('verified_at')
            ->values();

        $totalBiaya = (float) ($booking->total_harga ?? 0);
        $totalDibayar = (float) $payments->sum('amount');
        $sisaTagihan = max(0, $totalBiaya - $totalDibayar);

        return view('user.kuitansi', compact(
            'user', 'booking', 'company', 'payments', 'totalBiaya', 'totalDibayar', 'sisaTagihan'
        ));
    }

    /**
     * Tandai semua notifikasi jamaah sebagai sudah dibaca
     */
    public function markNotificationsRead(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        Notifikasi::where('id_pengguna', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    private function normalizePhoneNumber(?string $phone): ?string
    {
        $raw = preg_replace('/[^0-9]/', '', (string) $phone);
        if ($raw === '') {
            return null;
        }
        $raw = preg_replace('/^(?:62|0)+/', '', $raw);

        return $raw !== '' ? '+62' . $raw : null;
    }

    /**
     * Kompres gambar dokumen (JPG/PNG/WEBP) menggunakan PHP GD sebelum disimpan ke private storage.
     */
    private function storeCompressedDocument(\Illuminate\Http\UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || !extension_loaded('gd')) {
            return $file->store('dokumen', 'private');
        }

        $raw = @file_get_contents($file->getRealPath());
        $src = $raw ? @imagecreatefromstring($raw) : false;
        if (!$src) {
            return $file->store('dokumen', 'private');
        }

        $origW = imagesx($src);
        $origH = imagesy($src);
        $maxWidth = 1600;

        if ($origW > $maxWidth && $origW > 0) {
            $newW = $maxWidth;
            $newH = (int) round(($origH * $maxWidth) / $origW);
            $dst = imagecreatetruecolor($newW, $newH);
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $dst;
        }

        ob_start();
        imagejpeg($src, null, 75);
        $compressed = ob_get_clean();
        imagedestroy($src);

        if (!$compressed) {
            return $file->store('dokumen', 'private');
        }

        $path = 'dokumen/' . \Illuminate\Support\Str::random(40) . '.jpg';
        \Illuminate\Support\Facades\Storage::disk('private')->put($path, $compressed);

        return $path;
    }
}

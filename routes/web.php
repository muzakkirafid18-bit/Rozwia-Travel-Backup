<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Administrator\UserManagementController;
use App\Http\Controllers\Jamaah\JamaahController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Supervisor\HotelController;
use App\Http\Controllers\Supervisor\MaskapaiController;
use App\Http\Controllers\Supervisor\RekeningPerusahaanController;
use App\Http\Controllers\Supervisor\SupervisorDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// halaman publik
Route::get('/', [PublicController::class, 'home'])->name('landing');
Route::get('/paket-umroh', [PublicController::class, 'umroh'])->name('paket.umroh');
Route::get('/paket-haji', [PublicController::class, 'haji'])->name('paket.haji');
Route::get('/paket/detail/{id}', [PublicController::class, 'detail'])->name('paket.detail');
Route::get('/tentang-kami', [PublicController::class, 'tentang'])->name('tentang.kami');
Route::get('/faq', [PublicController::class, 'faq'])->name('faq');

// alias url biar nggak 404 kalau diakses lewat link pendek
Route::redirect('/tentang', '/tentang-kami');
Route::redirect('/paket', '/paket-umroh');
Route::redirect('/administrator/roles', '/administrator/role-matrix');
Route::redirect('/admin/aktivasi', '/admin/aktivasi-akun');
Route::redirect('/admin/kemenag-visa', '/admin/kemenag');

// pendaftaran & booking paket
Route::get('/paket/registrasi/{id}', function ($id) {
    $paket = \App\Models\Paket::with(['jenisLayanan', 'jadwals.maskapai', 'gambarPakets'])->findOrFail($id);
    $jadwals = $paket->jadwals;

    $package = [
        'id'       => $paket->id,
        'name'     => $paket->nama_paket,
        'type'     => $paket->kategori,
        'duration' => $jadwals->first() ? ($jadwals->first()->tanggal_keberangkatan->diffInDays($jadwals->first()->tanggal_pulang) . ' Hari') : '9 Hari',
        'badge'    => $paket->jenisLayanan?->nama_pelayanan ?? 'Reguler',
        'image'    => $paket->gambar_utama ?: 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&w=800&q=80',
        'description' => $paket->deskripsi ?? 'Paket umroh eksklusif bersama ROZWIA.',
        'price'    => \App\Helpers\RozwiaHelper::formatRupiah($paket->harga_dasar),
    ];

    return view('user.registrasi', compact('id', 'package', 'paket', 'jadwals'));
})->name('user.registrasi');

Route::post('/paket/registrasi/{id}', [JamaahController::class, 'storeBooking'])
    ->middleware('throttle:10,1')
    ->name('booking.store');

Route::get('/email/track/{token}.gif', function (string $token) {
    \App\Models\EmailLog::where('token', $token)
        ->whereNull('opened_at')
        ->update(['opened_at' => now()]);

    $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

    return response($gif, 200, [
        'Content-Type'  => 'image/gif',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
})->name('email.track');

// halaman konfirmasi & bayar DP (pakai signed url)
Route::middleware(['signed', 'throttle:20,1'])->group(function () {
    Route::get('/booking/pembayaran-dp/{kode_booking}', [JamaahController::class, 'showPublicDpForm'])->name('booking.dp.form');
    Route::post('/booking/pembayaran-dp/{kode_booking}', [JamaahController::class, 'storePublicDp'])->name('booking.dp.store');
});

// handle lupa password biar nggak 500 kalau smtp lagi bermasalah
Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
    $request->validate(['email' => 'required|email']);

    try {
        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('Lupa Password (SMTP terkendala): ' . $e->getMessage());
        return back()->with('status', 'Permintaan reset password telah kami catat. Silakan periksa kotak masuk/spam email Anda secara berkala atau hubungi admin.');
    }
})->middleware(['guest', 'throttle:6,1'])->name('password.email');

// redirect beranda sesuai role setelah login
Route::get('/login/direct', function (\Illuminate\Http\Request $request) {
    if (Auth::check()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect()->route('login');
})->name('login.direct');

Route::get('/home', function () {
    return redirect()->route(Auth::user()->dashboardRouteName());
})->middleware('auth')->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware('auth')->name('user.home');

// halaman wajib ganti password pas login pertama
Route::middleware(['auth', 'throttle:6,1'])->group(function () {
    Route::get('/akun/ganti-password', [\App\Http\Controllers\ForcePasswordController::class, 'show'])->name('password.force.show');
    Route::post('/akun/ganti-password', [\App\Http\Controllers\ForcePasswordController::class, 'update'])->name('password.force.update');
});

// route khusus super admin
Route::middleware(['auth', 'role:administrator'])->prefix('administrator')->name('administrator.')->group(function () {
    Route::get('/dashboard', [UserManagementController::class, 'index'])->name('dashboard');
    Route::get('/role-matrix', [UserManagementController::class, 'roleMatrix'])->name('role-matrix');

    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/role-matrix', [UserManagementController::class, 'updateRoleMatrix'])->name('role-matrix.update');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');
        Route::post('/users/{id}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});

// route khusus supervisor
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    // beranda supervisor
    Route::get('/dashboard', [SupervisorDashboardController::class, 'dashboard'])->name('dashboard');

    // profil perusahaan
    Route::get('/profile', [SupervisorDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [SupervisorDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/perusahaan', [SupervisorDashboardController::class, 'profile'])->name('perusahaan');

    // kelola paket haji & umroh
    Route::get('/paket', [SupervisorDashboardController::class, 'paket'])->name('paket');
    Route::post('/paket', [SupervisorDashboardController::class, 'storePaket'])->name('paket.store');
    Route::put('/paket/{id}', [SupervisorDashboardController::class, 'updatePaket'])->name('paket.update');
    Route::post('/paket/{id}/toggle', [SupervisorDashboardController::class, 'togglePaketStatus'])->name('paket.toggle');
    Route::delete('/paket/{id}', [SupervisorDashboardController::class, 'deletePaket'])->name('paket.delete');
    Route::get('/paket/tambah', [SupervisorDashboardController::class, 'paket'])->name('paket.tambah');

    // daftar akun jamaah
    Route::get('/users', [SupervisorDashboardController::class, 'users'])->name('users');
    Route::get('/users/export', [SupervisorDashboardController::class, 'exportUsers'])->name('users.export');
    Route::get('/akun', [SupervisorDashboardController::class, 'users'])->name('akun');

    // laporan total biaya
    Route::get('/biaya', [SupervisorDashboardController::class, 'biaya'])->name('biaya');
    Route::get('/biaya/export', [SupervisorDashboardController::class, 'exportBiaya'])->name('biaya.export');

    Route::get('/manifest', function () {
        return redirect()->route('supervisor.biaya');
    })->name('manifest');

    // master data rekening, maskapai, & hotel
    Route::resource('rekening', RekeningPerusahaanController::class);
    Route::resource('maskapai', MaskapaiController::class);
    Route::resource('hotel', HotelController::class);

    // logout supervisor
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

// route khusus admin operasional
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // pembayaran & aktivasi dipisah ke halaman masing-masing
    Route::get('/pembayaran', [AdminDashboardController::class, 'pembayaran'])->name('pembayaran');
    Route::get('/aktivasi-akun', [AdminDashboardController::class, 'aktivasiAkun'])->name('aktivasi');
    Route::post('/aktivasi-akun/{id}', [AdminDashboardController::class, 'activateAccount'])
        ->middleware('throttle:30,1')
        ->name('aktivasi.store');
    Route::delete('/aktivasi-akun/{id}', [AdminDashboardController::class, 'deleteJamaahAccount'])
        ->middleware('throttle:60,1')
        ->name('aktivasi.destroy');

    // menu operasional admin
    Route::get('/dokumen', [AdminDashboardController::class, 'dokumen'])->name('dokumen');
    Route::get('/kemenag', [AdminDashboardController::class, 'kemenag'])->name('kemenag');
    Route::post('/kemenag/{id}/update', [AdminDashboardController::class, 'updateKemenag'])->name('kemenag.update');

    Route::get('/hotel', [AdminDashboardController::class, 'hotel'])->name('hotel');
    Route::post('/hotel/jadwal/{id}/update', [AdminDashboardController::class, 'updateJadwalHotel'])->name('hotel.jadwal.update');
    Route::post('/hotel/jamaah/{id}/update', [AdminDashboardController::class, 'updateManifestJamaah'])->name('hotel.jamaah.update');

    Route::get('/manifest', [AdminDashboardController::class, 'manifest'])->name('manifest');
    Route::post('/manifest/send', [AdminDashboardController::class, 'sendManifest'])->name('manifest.send');

    // verifikasi / tolak pembayaran & konfirmasi via WA
    Route::post('/pembayaran/{id}/verify', [AdminDashboardController::class, 'verifyPayment'])->name('pembayaran.verify');
    Route::post('/pembayaran/{id}/reject', [AdminDashboardController::class, 'rejectPayment'])->name('pembayaran.reject');
    Route::post('/booking/{id}/confirm-payment', [AdminDashboardController::class, 'confirmWaPayment'])->name('booking.confirm-payment');
    Route::post('/jamaah/{id}/reset-password', [AdminDashboardController::class, 'resetJamaahPassword'])->name('jamaah.reset-password');

    // verifikasi & tolak dokumen jamaah
    Route::post('/dokumen/{id}/verify', [AdminDashboardController::class, 'verifyDokumen'])->name('dokumen.verify');
    Route::post('/dokumen/jamaah/{id}/verify-all', [AdminDashboardController::class, 'verifyAllJamaahDokumen'])->name('dokumen.verify-all');
    Route::post('/dokumen/{id}/reject', [AdminDashboardController::class, 'rejectDokumen'])->name('dokumen.reject');

    // ambil file bukti bayar & dokumen dari storage private
    Route::get('/pembayaran/{id}/download', [AdminDashboardController::class, 'downloadBukti'])->name('pembayaran.download');
    Route::get('/dokumen/{id}/download', [AdminDashboardController::class, 'downloadDokumen'])->name('dokumen.download');
});

// route khusus jamaah
Route::middleware(['auth', 'role:jamaah'])->group(function () {
    // beranda jamaah
    Route::get('/user/dashboard', [JamaahController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/jamaah/dashboard', [JamaahController::class, 'dashboard'])->name('jamaah.dashboard');
    Route::post('/user/notifikasi/read', [JamaahController::class, 'markNotificationsRead'])->name('user.notifikasi.read');

    // halaman dp & kuitansi
    Route::get('/user/formulir-dp', [JamaahController::class, 'showDpForm'])->name('user.formulir.dp');
    Route::get('/jamaah/formulir-dp', [JamaahController::class, 'showDpForm']);
    Route::post('/user/formulir-dp', [JamaahController::class, 'storeDp'])->name('user.formulir.dp.store');
    Route::get('/user/kuitansi', [JamaahController::class, 'cetakKuitansi'])->name('user.kuitansi');
    Route::get('/jamaah/kuitansi', [JamaahController::class, 'cetakKuitansi']);

    // upload dokumen persyaratan
    Route::get('/user/dokumen', [JamaahController::class, 'showDokumenForm'])->name('user.dokumen');
    Route::get('/jamaah/dokumen', [JamaahController::class, 'showDokumenForm']);
    Route::post('/user/dokumen', [JamaahController::class, 'storeDokumen'])->name('user.dokumen.store');

    // pantau status perjalanan & visa
    Route::get('/user/status-tracking', [JamaahController::class, 'statusTracking'])->name('user.tracking');
    Route::get('/jamaah/status-tracking', [JamaahController::class, 'statusTracking']);

    // profil & data anggota rombongan
    Route::get('/user/profile', [JamaahController::class, 'profile'])->name('user.profile');
    Route::get('/jamaah/profile', [JamaahController::class, 'profile']);
    Route::post('/user/jamaah/{id}/update', [JamaahController::class, 'updateJamaahDetail'])->name('user.jamaah.update');
});

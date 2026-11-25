<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembaruan Verifikasi Dokumen - ROZWIA</title>
</head>
<body style="margin:0; padding:0; background-color:#F4EFE6; font-family:'Segoe UI', Tahoma, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4EFE6; padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#0B3B24; padding:28px 32px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:22px; margin:0;">ROZWIA Islamic Travel</h1>
                            <p style="color:{{ $statusType === 'all_verified' ? '#4ade80' : '#fbbf24' }}; font-size:12px; margin:8px 0 0; letter-spacing:1.5px;">
                                {{ $statusType === 'all_verified' ? 'SELURUH DOKUMEN TERVERIFIKASI' : 'PERBAIKAN DOKUMEN DIPERLUKAN' }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px; font-size:14px; color:#333; line-height:1.6;">
                            <p style="margin:0 0 12px; font-size:16px; color:#1a1a1a;">
                                Assalamu'alaikum Warahmatullahi Wabarakatuh, <strong>{{ $sapaan }} {{ $user->name }}</strong>
                            </p>

                            @if($statusType === 'all_verified')
                                <p style="margin:0 0 18px;">
                                    Alhamdulillah! Seluruh dokumen persyaratan keberangkatan pada kode booking <strong>{{ $booking->kode_booking }}</strong>
                                    (untuk seluruh {{ $booking->jamaahDetails->count() }} anggota jamaah yang terdaftar) telah <strong>selesai diperiksa dan diverifikasi oleh tim admin</strong>.
                                </p>
                                <div style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px; margin-bottom:22px; color:#166534;">
                                    <strong>Status Berkas: ✅ LENGKAP &amp; DIVERIFIKASI</strong><br>
                                    <span style="font-size:13px;">Silakan pantau perkembangan pelunasan dan proses Surat Kemenag / Visa melalui Portal Jamaah ROZWIA.</span>
                                </div>
                            @else
                                <p style="margin:0 0 18px;">
                                    Tim verifikasi ROZWIA telah memeriksa berkas <strong>{{ $documentName }}</strong> milik
                                    <strong>{{ $jamaah?->nama_lengkap ?? $user->name }}</strong> (Kode Booking: <strong>{{ $booking->kode_booking }}</strong>), dan berkas tersebut memerlukan perbaikan:
                                </p>
                                <div style="background-color:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:16px; margin-bottom:22px; color:#92400e;">
                                    <strong>Catatan Admin:</strong>
                                    <p style="margin:6px 0 0;">{{ $adminNotes ?: 'Mohon unggah ulang berkas dengan foto/scan yang lebih jelas dan sesuai ketentuan.' }}</p>
                                </div>
                            @endif

                            <div style="text-align:center; margin:24px 0;">
                                <a href="{{ $loginUrl }}" style="display:inline-block; background-color:#0B3B24; color:#ffffff; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px;">
                                    Login ke ROZWIA
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f8f6f2; padding:18px; text-align:center; border-top:1px solid #e5e2dc; font-size:12px; color:#888;">
                            © {{ date('Y') }} ROZWIA Islamic Travel — PPIU Resmi Kementerian Agama RI
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

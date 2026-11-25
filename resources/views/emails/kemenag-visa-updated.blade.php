<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembaruan Status Surat Kemenag &amp; Visa - ROZWIA</title>
</head>
<body style="margin:0; padding:0; background-color:#F4EFE6; font-family:'Segoe UI', Tahoma, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4EFE6; padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#0B3B24; padding:28px 32px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:22px; margin:0;">ROZWIA Islamic Travel</h1>
                            <p style="color:#D4B996; font-size:12px; margin:8px 0 0; letter-spacing:1.5px;">PEMBARUAN SURAT KEMENAG &amp; VISA</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px; font-size:14px; color:#333; line-height:1.6;">
                            <p style="margin:0 0 12px; font-size:16px; color:#1a1a1a;">
                                Assalamu'alaikum Warahmatullahi Wabarakatuh, <strong>{{ $sapaan }} {{ $user->name }}</strong>
                            </p>
                            <p style="margin:0 0 20px;">
                                Terdapat pembaruan status administrasi Surat Kemenag &amp; Visa untuk anggota jamaah
                                <strong>{{ $jamaah->nama_lengkap }}</strong> (Kode Booking: <strong>{{ $booking->kode_booking }}</strong>):
                            </p>

                            <table width="100%" cellpadding="10" cellspacing="0" style="background-color:#f8f6f2; border-radius:12px; font-size:14px; margin-bottom:24px;">
                                <tr>
                                    <td style="color:#666; width:170px;">Surat Kemenag</td>
                                    <td style="font-weight:bold; color:{{ $jamaah->status_kemenag === 'Terbit' ? '#15803d' : ($jamaah->status_kemenag === 'Ditolak' ? '#b91c1c' : '#0B3B24') }};">
                                        {{ $jamaah->status_kemenag }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:#666;">Status Visa</td>
                                    <td style="font-weight:bold; color:{{ $jamaah->status_visa === 'Terbit' ? '#15803d' : ($jamaah->status_visa === 'Ditolak' ? '#b91c1c' : '#0B3B24') }};">
                                        {{ $jamaah->status_visa }}
                                    </td>
                                </tr>
                                @if($jamaah->status_visa === 'Terbit' && !empty($jamaah->nomor_visa))
                                <tr>
                                    <td style="color:#666;">Nomor Visa</td>
                                    <td style="font-weight:bold; color:#0B3B24;">{{ $jamaah->nomor_visa }}</td>
                                </tr>
                                @endif
                                @if($jamaah->status_visa === 'Terbit' && !empty($jamaah->tanggal_terbit_visa))
                                <tr>
                                    <td style="color:#666;">Tanggal Terbit Visa</td>
                                    <td style="font-weight:bold; color:#0B3B24;">{{ $jamaah->tanggal_terbit_visa->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                            </table>

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

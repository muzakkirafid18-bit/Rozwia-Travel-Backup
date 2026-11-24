<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Informasi Akun Portal Jamaah - ROZWIA</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4efe6; color: #1c1917; margin: 0; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e7e5e4;">
        <div style="background-color: #063B25; color: #ffffff; padding: 24px 32px;">
            <h1 style="margin: 0; font-size: 20px; letter-spacing: 1px;">ROZWIA ISLAMIC TRAVEL</h1>
            <p style="margin: 4px 0 0; font-size: 12px; color: #fde68a;">{{ $reason }}</p>
        </div>

        <div style="padding: 28px 32px; line-height: 1.6; font-size: 14px;">
            <p>Assalamu'alaikum Warahmatullahi Wabarakatuh, <strong>{{ $sapaan ?? 'Bapak/Ibu' }} {{ $user->name }}</strong>,</p>
            @if($user->isJamaah())
                <p>Berikut kami sampaikan informasi akun Portal Jamaah ROZWIA milik {{ $sapaan ?? 'Bapak/Ibu' }} untuk melengkapi data jamaah, mengunggah berkas persyaratan, serta memantau status keberangkatan:</p>
            @else
                <p>Berikut kami sampaikan informasi akses akun <strong>{{ $user->roleLabel() }}</strong> ROZWIA Islamic Travel milik {{ $sapaan ?? 'Bapak/Ibu' }} untuk masuk ke panel sistem operasional:</p>
            @endif

            <div style="background-color: #f9f6f0; border: 1px solid #e5d3b3; border-radius: 12px; padding: 16px 20px; margin: 20px 0;">
                <p style="margin: 4px 0;"><strong>Link Login:</strong> <a href="{{ $loginUrl ?? route('login') }}" style="color: #063B25;">{{ $loginUrl ?? route('login') }}</a></p>
                <p style="margin: 4px 0;"><strong>Email:</strong> {{ $user->email }}</p>
                <p style="margin: 4px 0;"><strong>Password Sementara:</strong> <code style="background: #063B25; color: #fde68a; padding: 3px 8px; border-radius: 6px; font-size: 14px; letter-spacing: 0.5px;">{{ $plainPassword }}</code></p>
            </div>

            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ $loginUrl ?? route('login') }}" target="_blank" style="display:inline-block; background-color:#063B25; color:#ffffff; padding:11px 26px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px;">
                    Login ke ROZWIA
                </a>
            </div>

            <p style="font-size: 12px; color: #78716c;">Demi keamanan akun Anda, sistem akan meminta Anda mengganti password sementara ini pada saat login pertama.</p>

            <p style="margin-top: 24px;">Jazakumullah khairan katsiran.<br><strong>Tim Operasional ROZWIA Islamic Travel</strong></p>
        </div>
    </div>
    @if(!empty($trackingToken))
        <img src="{{ route('email.track', $trackingToken) }}" width="1" height="1" alt="" style="display:none;width:1px;height:1px;">
    @endif
</body>
</html>

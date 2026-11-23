<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:24px;background:#F3F4FA;font-family:Arial,Helvetica,sans-serif;color:#0F1033;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border:1px solid #0F1033;border-radius:12px;">
        <tr><td style="padding:28px 28px 8px;">
            <p style="margin:0 0 16px;font-size:18px;font-weight:bold;">Routerverse</p>
            <p style="margin:0 0 12px;font-size:16px;line-height:1.5;">Halo {{ $user->name }}, ini kode untuk masuk ke akun Anda:</p>
            <p style="margin:16px 0;font-size:34px;letter-spacing:8px;font-weight:bold;">{{ $code }}</p>
            <p style="margin:0 0 12px;font-size:15px;line-height:1.5;color:#4A4D6E;">Kode berlaku {{ $minutes }} menit dan hanya bisa dipakai sekali.</p>
        </td></tr>
        <tr><td style="padding:8px 28px 28px;">
            <p style="margin:0;font-size:14px;line-height:1.5;color:#4A4D6E;">Jika Anda tidak sedang mencoba masuk, abaikan email ini dan segera ganti password Anda. Admin Routerverse tidak pernah meminta kode ini.</p>
        </td></tr>
    </table>
</td></tr></table>
</body>
</html>

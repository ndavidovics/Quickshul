<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your QuickShul Portal Link</title>
</head>
<body style="margin:0;padding:0;background:#f4f1eb;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#2c2c2c">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f1eb;padding:40px 20px">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px">

    {{-- Header --}}
    <tr><td style="text-align:center;padding-bottom:28px">
        <div style="font-family:Georgia,'Times New Roman',serif;font-size:26px;font-weight:700;color:#1a2d5a;letter-spacing:-0.01em">
            Quick<span style="color:#c9a84c">Shul</span>
        </div>
        <div style="font-size:11px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:#c9a84c;margin-top:4px">
            Member Portal
        </div>
    </td></tr>

    {{-- Card --}}
    <tr><td style="background:#ffffff;border-radius:12px;border:1px solid rgba(201,168,76,0.2);padding:36px 40px;box-shadow:0 4px 24px rgba(26,45,90,0.07)">

        <p style="font-family:Georgia,'Times New Roman',serif;font-size:20px;font-weight:600;color:#1a2d5a;margin:0 0 12px">
            Here's your portal link
        </p>
        <p style="font-size:14px;color:#5a5a5a;line-height:1.6;margin:0 0 28px">
            We received a request to find the member portal associated with
            <strong>{{ $email }}</strong>. Click the link below to sign in.
        </p>

        @foreach($portals as $portal)
        <div style="margin-bottom:{{ !$loop->last ? '16px' : '0' }}">
            @if(count($portals) > 1)
            <div style="font-size:12px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px">
                {{ $portal['name'] }}
            </div>
            @endif
            <a href="{{ $portal['url'] }}"
               style="display:block;background:linear-gradient(135deg,#1a2d5a 0%,#111d3c 100%);color:#ffffff;text-decoration:none;text-align:center;padding:14px 24px;border-radius:8px;font-size:15px;font-weight:600;letter-spacing:0.02em">
                Sign in to {{ $portal['name'] }} →
            </a>
        </div>
        @endforeach

        <p style="font-size:12px;color:#aaa;margin:24px 0 0;line-height:1.5">
            If you didn't request this email, you can safely ignore it.
            This link is for the member portal only — no account was created or modified.
        </p>
    </td></tr>

    {{-- Footer --}}
    <tr><td style="text-align:center;padding-top:24px;font-size:11px;color:#aaa;line-height:1.6">
        QuickShul — Member Portal Platform<br>
        <a href="https://quickshul.com" style="color:#c9a84c;text-decoration:none">quickshul.com</a>
    </td></tr>

</table>
</td></tr>
</table>
</body>
</html>

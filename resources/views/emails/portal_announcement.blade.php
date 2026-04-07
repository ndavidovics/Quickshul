<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New {{ $tenant->name ?? config('app.name') }} Member Portal Now Available</title>
</head>
<body style="margin:0;padding:0;background:#f0ede8;font-family:'Segoe UI',Arial,sans-serif;color:#1a2d5a">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0ede8;padding:32px 16px">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">

    {{-- Header --}}
    <tr>
        <td style="background:#1a2d5a;padding:28px 36px;border-radius:10px 10px 0 0;text-align:center">
            <div style="font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:700;color:#c9a84c;letter-spacing:0.02em">
                {{ $tenant->name ?? config('app.name') }}
            </div>
            <div style="font-size:11px;color:rgba(255,255,255,0.55);letter-spacing:0.12em;text-transform:uppercase;margin-top:6px">
                {{ $tenant->tagline ?? 'Torah &bull; Tefillah &bull; Tradition' }}
            </div>
        </td>
    </tr>

    {{-- Body --}}
    <tr>
        <td style="background:#ffffff;padding:36px 36px 28px;border-left:1px solid #e8e4dc;border-right:1px solid #e8e4dc">

            <p style="font-family:Georgia,serif;font-size:20px;color:#1a2d5a;margin:0 0 20px">
                Dear {{ $greeting }},
            </p>

            <p style="font-size:15px;line-height:1.8;color:#333;margin:0 0 16px">
                We are excited to introduce our new {{ $tenant->name ?? config('app.name') }} Member Portal, now available at:
            </p>

            {{-- Portal link --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px">
                <tr>
                    <td align="center">
                        <a href="{{ $tenant->portalUrl() ?? '#' }}"
                           style="display:inline-block;background:#c9a84c;color:#1a2d5a;text-decoration:none;font-weight:700;font-size:16px;padding:14px 40px;border-radius:6px;letter-spacing:0.03em">
                            Log In to the Member Portal
                        </a>
                        <div style="margin-top:8px;font-size:13px;color:#888">
                            <a href="{{ $tenant->portalUrl() ?? '#' }}" style="color:#1a2d5a">{{ $tenant->portalUrl() ?? '#' }}</a>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- How to log in --}}
            <div style="background:#faf8f4;border-left:4px solid #c9a84c;padding:18px 20px;margin-bottom:24px;border-radius:0 6px 6px 0">
                <div style="font-weight:700;font-size:14px;color:#1a2d5a;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em">How to Log In</div>
                <ul style="margin:0;padding-left:20px;font-size:14px;line-height:1.9;color:#333">
                    <li>If you use Gmail, you can simply click <strong>&ldquo;Sign in with Google.&rdquo;</strong></li>
                    <li>Otherwise, click <strong>&ldquo;Forgot Password&rdquo;</strong> and enter the email address you receive shul emails from. You will receive a link to create a password and log in.</li>
                </ul>
            </div>

            {{-- What you can do --}}
            <div style="margin-bottom:24px">
                <div style="font-weight:700;font-size:14px;color:#1a2d5a;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em">What You Can Do in the Portal</div>
                <ul style="margin:0;padding-left:20px;font-size:14px;line-height:1.9;color:#333">
                    <li>Pay off pledges and donations</li>
                    <li>View your past payments</li>
                    <li>Update your family information</li>
                    <li>Add birthdays and contact details</li>
                </ul>
            </div>

            <p style="font-size:15px;line-height:1.8;color:#333;margin:0 0 12px">
                Keeping your information up to date will help the shul serve the community better, including helping us with:
            </p>
            <ul style="margin:0 0 24px;padding-left:20px;font-size:14px;line-height:1.9;color:#333">
                <li>Aliyah coordination</li>
                <li>Hebrew birthday reminders</li>
                <li>Yahrtzeit reminders</li>
                <li>And many other things we hope to build in the future</li>
            </ul>

            <p style="font-size:15px;line-height:1.8;color:#333;margin:0 0 16px">
                Please take a few minutes to log in and review your family information.
            </p>

            <p style="font-size:15px;line-height:1.8;color:#333;margin:0 0 24px">
                If you notice any errors or have trouble accessing your account, please contact our office at
                <a href="mailto:{{ $tenant->org_email ?? '' }}" style="color:#1a2d5a;font-weight:600">{{ $tenant->org_email ?? '' }}</a>.
            </p>

            <p style="font-size:15px;line-height:1.8;color:#333;margin:0 0 8px">
                Thank you for helping us make the shul run more smoothly for everyone.
            </p>

            <p style="font-size:15px;line-height:1.8;color:#333;margin:0 0 4px">Kol Tuv,</p>
            <p style="font-family:Georgia,serif;font-size:16px;font-weight:700;color:#1a2d5a;margin:0">{{ $tenant->name ?? config('app.name') }}</p>

        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#f0ede8;padding:18px 36px;border-radius:0 0 10px 10px;border:1px solid #e8e4dc;border-top:none;text-align:center">
            <p style="font-size:12px;color:#999;margin:0">
                You are receiving this email as a member of {{ $tenant->name ?? config('app.name') }}.<br>
                Questions? Contact us at <a href="mailto:{{ $tenant->org_email ?? '' }}" style="color:#888">{{ $tenant->org_email ?? '' }}</a>
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>

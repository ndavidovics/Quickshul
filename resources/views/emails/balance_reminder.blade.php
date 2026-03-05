<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Outstanding Balance — Young Israel of Memphis</title>
</head>
<body style="margin:0;padding:0;background:#f0ede8;font-family:'Segoe UI',Arial,sans-serif;color:#1a2d5a">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0ede8;padding:32px 16px">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">

    {{-- Header --}}
    <tr>
        <td style="background:#1a2d5a;padding:28px 36px;border-radius:10px 10px 0 0;text-align:center">
            <div style="font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:700;color:#c9a84c;letter-spacing:0.02em">
                Young Israel of Memphis
            </div>
            <div style="font-size:11px;color:rgba(255,255,255,0.55);letter-spacing:0.12em;text-transform:uppercase;margin-top:6px">
                Torah &bull; Tefillah &bull; Tradition
            </div>
        </td>
    </tr>

    {{-- Body --}}
    <tr>
        <td style="background:#ffffff;padding:36px 36px 28px;border-left:1px solid #e8e4dc;border-right:1px solid #e8e4dc">

            <p style="font-family:Georgia,serif;font-size:20px;color:#1a2d5a;margin:0 0 20px">
                Dear {{ $greeting }},
            </p>

            <div style="font-size:15px;line-height:1.8;color:#333;margin:0 0 24px;white-space:pre-line">{{ $intro }}</div>

            {{-- CTA button (above table) --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px">
                <tr>
                    <td align="center">
                        <a href="{{ $paymentUrl }}"
                           style="display:inline-block;background:#c9a84c;color:#1a2d5a;text-decoration:none;font-weight:700;font-size:15px;padding:14px 36px;border-radius:6px;letter-spacing:0.03em">
                            Pay Online Now
                        </a>
                    </td>
                </tr>
            </table>

            {{-- Pledges table --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;border-collapse:collapse">
                <tr style="background:#1a2d5a">
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:#c9a84c;letter-spacing:0.05em;text-transform:uppercase">Description</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:#c9a84c;letter-spacing:0.05em;text-transform:uppercase">Pledged</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:#c9a84c;letter-spacing:0.05em;text-transform:uppercase">Balance Due</th>
                </tr>
                @foreach($openPledges as $i => $pledge)
                <tr style="background:{{ $i % 2 === 0 ? '#faf8f4' : '#ffffff' }}">
                    <td style="padding:11px 14px;font-size:14px;color:#1a2d5a;border-bottom:1px solid #e8e4dc">
                        {{ $pledge->description ?: 'Pledge' }}
                        <div style="font-size:11px;color:#999;margin-top:2px">{{ $pledge->invoice_date->format('M j, Y') }}</div>
                    </td>
                    <td style="padding:11px 14px;font-size:14px;text-align:right;color:#555;border-bottom:1px solid #e8e4dc">${{ number_format($pledge->amount, 2) }}</td>
                    <td style="padding:11px 14px;font-size:14px;font-weight:700;text-align:right;color:#1a2d5a;border-bottom:1px solid #e8e4dc">${{ number_format($pledge->balance, 2) }}</td>
                </tr>
                @endforeach
                <tr style="background:#1a2d5a">
                    <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#ffffff">Total Outstanding</td>
                    <td></td>
                    <td style="padding:12px 14px;font-size:16px;font-weight:700;text-align:right;color:#c9a84c">${{ number_format($totalBalance, 2) }}</td>
                </tr>
            </table>

            {{-- CTA button --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px">
                <tr>
                    <td align="center">
                        <a href="{{ $paymentUrl }}"
                           style="display:inline-block;background:#c9a84c;color:#1a2d5a;text-decoration:none;font-weight:700;font-size:15px;padding:14px 36px;border-radius:6px;letter-spacing:0.03em">
                            Pay Online Now
                        </a>
                        <div style="font-size:11px;color:#999;margin-top:8px">
                            Secure link &bull; No account required &bull; Expires {{ $expiresAt }}
                        </div>
                    </td>
                </tr>
            </table>

            <p style="font-size:14px;line-height:1.8;color:#555;margin:0 0 16px">
                If you have already sent a payment or have any questions about your account, please do not hesitate
                to reach out to our office. We are always happy to assist and deeply appreciate your dedication to
                our shul and community.
            </p>

            <p style="font-size:15px;line-height:1.8;color:#333;margin:0 0 4px">
                With blessings and gratitude,
            </p>
            <p style="font-size:15px;font-weight:600;color:#1a2d5a;margin:0">
                The Young Israel of Memphis
            </p>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#1a2d5a;padding:20px 36px;border-radius:0 0 10px 10px;text-align:center">
            <p style="font-size:12px;color:rgba(255,255,255,0.5);margin:0;line-height:1.8">
                531 S Yates Rd &bull; Memphis, TN 38120<br>
                <a href="mailto:exec@yiom.org" style="color:#c9a84c;text-decoration:none">exec@yiom.org</a>
                &bull; (901) 761-6060
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>

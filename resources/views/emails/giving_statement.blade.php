<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Giving Statement — Young Israel of Memphis</title>
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
            <div style="font-size:13px;color:rgba(255,255,255,0.75);margin-top:10px">
                Giving Statement &bull; {{ $periodLabel }}
            </div>
        </td>
    </tr>

    {{-- Body --}}
    <tr>
        <td style="background:#ffffff;padding:36px 36px 28px;border-left:1px solid #e8e4dc;border-right:1px solid #e8e4dc">

            <p style="font-family:Georgia,serif;font-size:20px;color:#1a2d5a;margin:0 0 20px">
                Dear {{ $greeting }},
            </p>

            {{-- Admin-editable intro (merge tags already replaced) --}}
            <div style="font-size:15px;line-height:1.8;color:#333;margin-bottom:28px;white-space:pre-line">{{ $introText }}</div>

            {{-- Payments table --}}
            @if($payments->isNotEmpty())
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;border-collapse:collapse">
                <tr style="background:#1a2d5a">
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:#c9a84c;letter-spacing:0.05em;text-transform:uppercase">Date</th>
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:#c9a84c;letter-spacing:0.05em;text-transform:uppercase">Description</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:#c9a84c;letter-spacing:0.05em;text-transform:uppercase">Amount</th>
                </tr>
                @foreach($payments as $i => $payment)
                <tr style="background:{{ $i % 2 === 0 ? '#faf8f4' : '#ffffff' }}">
                    <td style="padding:10px 14px;font-size:13px;color:#555;border-bottom:1px solid #e8e4dc;white-space:nowrap">{{ $payment->payment_date->format('M j, Y') }}</td>
                    <td style="padding:10px 14px;font-size:13px;color:#1a2d5a;border-bottom:1px solid #e8e4dc">{{ $payment->description ?: 'Contribution' }}</td>
                    <td style="padding:10px 14px;font-size:13px;font-weight:600;text-align:right;color:#1a2d5a;border-bottom:1px solid #e8e4dc">${{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr style="background:#1a2d5a">
                    <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#ffffff" colspan="2">Total Contributions</td>
                    <td style="padding:12px 14px;font-size:16px;font-weight:700;text-align:right;color:#c9a84c">${{ number_format($totalAmount, 2) }}</td>
                </tr>
            </table>

            <p style="font-size:12px;color:#999;font-style:italic;margin:0 0 24px;line-height:1.7">
                Young Israel of Memphis is a 501(c)(3) tax-exempt organization. No goods or services were provided
                in exchange for these contributions. Please retain this statement for your tax records.
                EIN: 16-1618003
            </p>
            @else
            <p style="font-size:14px;color:#888;font-style:italic;margin:0 0 24px">
                No payments were recorded during this period.
            </p>
            @endif

            <p style="font-size:14px;line-height:1.8;color:#555;margin:0 0 16px">
                If you have any questions about this statement, please contact our office and we will be happy to assist you.
            </p>

            <p style="font-size:15px;line-height:1.8;color:#333;margin:0 0 4px">With blessings and gratitude,</p>
            <p style="font-size:15px;font-weight:600;color:#1a2d5a;margin:0">The Young Israel of Memphis</p>
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

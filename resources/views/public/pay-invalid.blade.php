<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Link Expired — {{ $tenant->name ?? config('app.name') }}</title>
<style>
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0ede8;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding:2rem;color:#1a2d5a}
.logo{font-family:Georgia,serif;font-size:1.4rem;font-weight:700;color:#1a2d5a;margin-bottom:0.25rem}
.sub{font-size:0.78rem;color:#c9a84c;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:2rem}
.card{background:#fff;border-radius:10px;padding:2rem 2.5rem;box-shadow:0 2px 16px rgba(26,45,90,0.08);text-align:center;max-width:420px}
h2{font-family:Georgia,serif;font-size:1.25rem;margin-bottom:0.75rem}
p{color:#666;font-size:0.9rem;line-height:1.7}
a{color:#c9a84c}
</style>
</head>
<body>
<div class="logo">{{ $tenant->name ?? config('app.name') }}</div>
<div class="sub">{{ $tenant->tagline ?? '' }}</div>
<div class="card">
    <h2>This link has expired</h2>
    <p>Payment links are valid for 30 days. Please contact the office to receive a new payment link.<br><br>
    @if($tenant->org_email ?? null)<a href="mailto:{{ $tenant->org_email }}">{{ $tenant->org_email }}</a>@if($tenant->org_phone ?? null) &bull; {{ $tenant->org_phone }}@endif@endif</p>
</div>
</body>
</html>

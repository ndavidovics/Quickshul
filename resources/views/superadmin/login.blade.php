<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin — QuickShul</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy: #0d1829; --navy-mid: #1a2d5a; --gold: #c9a84c;
            --text: #e8e4dc; --text-muted: #8899bb; --border: rgba(201,168,76,0.2);
        }
        body {
            min-height: 100vh;
            background: var(--navy);
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(26,45,90,0.5) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.06) 0%, transparent 50%);
            font-family: 'Inter', sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .wrapper { width: 100%; max-width: 400px; }
        .brand { text-align: center; margin-bottom: 2.5rem; }
        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem; font-weight: 700; color: var(--gold);
            letter-spacing: -0.01em;
        }
        .brand-badge {
            display: inline-block;
            font-size: 0.65rem; font-weight: 600; letter-spacing: 0.15em;
            text-transform: uppercase; color: rgba(255,255,255,0.5);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 4px;
            padding: 0.2rem 0.55rem; margin-top: 0.5rem;
        }
        .card {
            background: #162240;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 2.25rem 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem; font-weight: 600; color: #fff;
            margin-bottom: 1.75rem; text-align: center;
        }
        label {
            display: block; font-size: 0.75rem; font-weight: 600;
            color: var(--text-muted); margin-bottom: 0.4rem;
            letter-spacing: 0.06em; text-transform: uppercase;
        }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 0.7rem 0.9rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
            font-size: 0.9rem; font-family: 'Inter', sans-serif;
            color: var(--text); outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            margin-bottom: 1rem;
        }
        input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
        }
        .btn {
            width: 100%; padding: 0.8rem;
            background: linear-gradient(135deg, var(--gold), #a8882a);
            border: none; border-radius: 8px;
            font-size: 0.9rem; font-weight: 700;
            color: #0d1829; font-family: 'Inter', sans-serif;
            cursor: pointer; letter-spacing: 0.03em;
            transition: filter 0.2s, transform 0.1s;
        }
        .btn:hover { filter: brightness(1.1); transform: translateY(-1px); }
        .alert {
            background: rgba(231,76,60,0.12); border: 1px solid rgba(231,76,60,0.3);
            border-radius: 8px; padding: 0.7rem 0.9rem;
            font-size: 0.82rem; color: #f08080; margin-bottom: 1.25rem;
        }
        .back { text-align: center; margin-top: 1.5rem; font-size: 0.78rem; }
        .back a { color: var(--text-muted); text-decoration: none; }
        .back a:hover { color: var(--gold); }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="brand">
            <div class="brand-name">QuickShul</div>
            <div><span class="brand-badge">Super Admin</span></div>
        </div>
        <div class="card">
            <div class="card-title">Administrator Sign In</div>

            @if($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('superadmin.login.submit') }}">
                @csrf
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="admin@quickshul.com"
                       autocomplete="email" autofocus required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••"
                       autocomplete="current-password" required>

                <button type="submit" class="btn">Sign In</button>
            </form>
        </div>
        <div class="back"><a href="{{ route('home') }}">← Back to QuickShul</a></div>
    </div>
</body>
</html>

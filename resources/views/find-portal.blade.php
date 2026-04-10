<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Portal — QuickShul</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy: #1a2d5a; --navy-dark: #111d3c; --gold: #c9a84c;
            --cream: #faf8f4; --text: #2c2c2c; --text-light: #6b6b6b;
            --border: #ddd6c8; --white: #fff;
        }
        body {
            min-height: 100vh;
            background: var(--cream);
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(26,45,90,0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.08) 0%, transparent 50%);
            font-family: 'Inter', sans-serif;
            color: var(--text);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .wrapper { width: 100%; max-width: 440px; }
        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand a {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--navy);
            text-decoration: none;
            letter-spacing: -0.01em;
        }
        .brand a span { color: var(--gold); }
        .brand-sub {
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--gold);
            margin-top: 0.2rem;
        }
        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 2.5rem 2.25rem;
            box-shadow: 0 4px 6px rgba(26,45,90,0.04), 0 10px 40px rgba(26,45,90,0.08);
            border: 1px solid rgba(201,168,76,0.15);
        }
        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.5rem;
        }
        .card-desc {
            font-size: 0.875rem;
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }
        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.4rem;
            letter-spacing: 0.02em;
        }
        input[type="email"] {
            width: 100%;
            padding: 0.72rem 0.9rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.925rem;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: var(--cream);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            margin-bottom: 1.25rem;
        }
        input[type="email"]:focus {
            border-color: var(--navy);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(26,45,90,0.08);
        }
        .btn {
            width: 100%;
            padding: 0.8rem 1rem;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-dark) 100%);
            border: none;
            border-radius: 10px;
            font-size: 0.925rem;
            font-weight: 600;
            color: var(--white);
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            letter-spacing: 0.02em;
        }
        .btn:hover {
            box-shadow: 0 4px 15px rgba(26,45,90,0.35);
            transform: translateY(-1px);
        }
        .success-box {
            text-align: center;
            padding: 0.5rem 0;
        }
        .success-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        .success-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.6rem;
        }
        .success-desc {
            font-size: 0.875rem;
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .try-again {
            font-size: 0.82rem;
            color: var(--text-light);
        }
        .try-again a { color: var(--navy); font-weight: 500; text-decoration: none; }
        .try-again a:hover { color: var(--gold); }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.82rem;
            color: var(--text-light);
        }
        .back-link a { color: var(--navy); font-weight: 500; text-decoration: none; }
        .back-link a:hover { color: var(--gold); }
        .ornament {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.72rem;
            color: var(--text-light);
            letter-spacing: 0.05em;
        }
        .ornament span { color: var(--gold); font-family: 'Playfair Display', serif; font-size: 1rem; }
    </style>
</head>
<body>
    <div class="wrapper">

        <div class="brand">
            <div><a href="{{ route('home') }}">Quick<span>Shul</span></a></div>
            <div class="brand-sub">Member Portal</div>
        </div>

        <div class="card">
            @if(session('sent'))
                <div class="success-box">
                    <span class="success-icon">✉️</span>
                    <div class="success-title">Check your inbox</div>
                    <p class="success-desc">
                        If your email address is linked to a shul portal, you'll receive a message
                        with a direct login link within a minute or two.
                    </p>
                    <p class="try-again">
                        Didn't get it? Check your spam folder, or
                        <a href="{{ route('find-portal') }}">try again</a>.
                    </p>
                </div>
            @else
                <div class="card-title">Find your portal</div>
                <p class="card-desc">
                    Enter the email address you use with your synagogue.
                    We'll send you a direct link to sign in.
                </p>

                @if($errors->any())
                    <div style="background:#fdf3f2;border:1px solid #f5c6c2;border-left:3px solid #c0392b;border-radius:8px;padding:0.7rem 1rem;font-size:0.85rem;color:#c0392b;margin-bottom:1.25rem">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('find-portal.submit') }}">
                    @csrf
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           autocomplete="email"
                           autofocus required>
                    <button type="submit" class="btn">Send Me My Portal Link</button>
                </form>
            @endif
        </div>

        <div class="back-link">
            <a href="{{ route('home') }}">← Back to QuickShul</a>
        </div>

        <div class="ornament">
            <span>&#10022;</span> &nbsp; QuickShul &nbsp; <span>&#10022;</span>
        </div>

    </div>
</body>
</html>

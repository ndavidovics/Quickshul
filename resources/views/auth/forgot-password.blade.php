<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password &mdash; {{ $tenant->name ?? config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" sizes="192x192" href="/favicon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --navy: #1a2d5a;
            --navy-dark: #111d3c;
            --gold: #c9a84c;
            --gold-light: #e0c06a;
            --cream: #faf8f4;
            --text: #2c2c2c;
            --text-light: #6b6b6b;
            --border: #ddd6c8;
            --error: #c0392b;
            --success: #27ae60;
            --white: #ffffff;
        }

        body {
            min-height: 100vh;
            background-color: var(--cream);
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(26, 45, 90, 0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201, 168, 76, 0.08) 0%, transparent 50%);
            font-family: 'Inter', sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .page-wrapper {
            width: 100%;
            max-width: 460px;
        }

        /* Header with logo */
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container {
            display: inline-block;
            background: var(--white);
            border-radius: 50%;
            padding: 12px;
            box-shadow: 0 4px 20px rgba(26, 45, 90, 0.12);
            margin-bottom: 1.25rem;
            border: 3px solid var(--gold);
        }

        .logo-container img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            display: block;
            border-radius: 50%;
        }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.2;
            margin-bottom: 0.25rem;
        }

        .brand-subtitle {
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--gold);
        }

        /* Card */
        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 2.5rem 2.25rem;
            box-shadow:
                0 4px 6px rgba(26, 45, 90, 0.04),
                0 10px 40px rgba(26, 45, 90, 0.08);
            border: 1px solid rgba(201, 168, 76, 0.15);
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.35rem;
        }

        .card-desc {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.1rem;
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
            padding: 0.7rem 0.9rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.925rem;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: var(--cream);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        input[type="email"]:focus {
            border-color: var(--navy);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(26, 45, 90, 0.08);
        }

        input[type="email"].is-invalid {
            border-color: var(--error);
        }

        .field-error {
            font-size: 0.78rem;
            color: var(--error);
            margin-top: 0.35rem;
        }

        /* Alert */
        .alert {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            border-left: 3px solid;
        }

        .alert-error {
            background: #fdf3f2;
            border-color: var(--error);
            color: var(--error);
        }

        .alert-success {
            background: #f0f9f7;
            border-color: var(--success);
            color: #1e6b54;
        }

        /* Submit button */
        .btn-primary {
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

        .btn-primary:hover {
            background: linear-gradient(135deg, #22397a 0%, #1a2d5a 100%);
            box-shadow: 0 4px 15px rgba(26, 45, 90, 0.35);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Back link */
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: var(--navy);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            color: var(--gold);
        }

        .ornament {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.72rem;
            color: var(--text-light);
            letter-spacing: 0.05em;
        }

        .ornament span {
            color: var(--gold);
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
        }

        .legal-links {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.72rem;
            color: var(--text-light);
        }

        .legal-links a {
            color: var(--text-light);
            text-decoration: none;
        }

        .legal-links a:hover {
            color: var(--navy);
            text-decoration: underline;
        }

        .legal-links .sep {
            margin: 0 0.5rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">

        <!-- Brand Header -->
        <div class="brand-header">
            <div class="logo-container">
                <img src="{{ $tenant->logo_url ?? asset('img/quickshul-logo.svg') }}"
                     alt="{{ $tenant->name ?? config('app.name') }}">
            </div>
            <div class="brand-name">{{ $tenant->name ?? config('app.name') }}</div>
            <div class="brand-subtitle">Member Portal</div>
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-title">Reset Your Password</div>
            <p class="card-desc">Enter your email address and we'll send you a link to reset your password.</p>

            <!-- Status Message -->
            @if ($status = session('status'))
                <div class="alert alert-success">
                    {{ __($status) }}
                </div>
            @endif

            <!-- Errors -->
            @if ($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        autocomplete="email"
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        required
                        autofocus
                    >
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">Send Reset Link</button>
                <a href="{{ route('login') }}" class="back-link">← Back to login</a>
            </form>
        </div>

        <div class="ornament">
            <span>&#10022;</span> &nbsp; {{ $tenant->name ?? config('app.name') }} &nbsp; <span>&#10022;</span>
        </div>

        <div class="legal-links">
            <a href="{{ route('agreement') }}">End User License Agreement</a>
            <span class="sep">&bull;</span>
            <a href="{{ route('privacy') }}">Privacy Policy</a>
        </div>

    </div>
</body>
</html>

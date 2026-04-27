<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickShul — Free Synagogue Management Software</title>
    <meta name="description" content="QuickShul is free, open-source synagogue management software. Member & family management, pledge tracking, email tools, Hebrew calendar, yahrtzeit reminders, and QuickBooks sync. Free forever.">
    <meta name="keywords" content="synagogue management software, shul management software, Jewish community software, synagogue member portal, synagogue QuickBooks integration, free synagogue software, open source synagogue, congregation management, pledge tracking software">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="author" content="QuickShul">

    <!-- Canonical -->
    <link rel="canonical" href="https://quickshul.com/">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://quickshul.com/">
    <meta property="og:site_name" content="QuickShul">
    <meta property="og:locale" content="en_US">
    <meta property="og:title" content="QuickShul — Free Synagogue Management Software">
    <meta property="og:description" content="Free, open-source synagogue management software. Member & family management, pledge tracking, email tools, Hebrew calendar, yahrtzeit reminders, and QuickBooks sync.">
    <meta property="og:image" content="https://quickshul.com/img/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="QuickShul — Modern member management for your shul">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="QuickShul — Free Synagogue Management Software">
    <meta name="twitter:description" content="Free, open-source synagogue management software. Member & family management, pledge tracking, email tools, Hebrew calendar, and QuickBooks sync.">
    <meta name="twitter:image" content="https://quickshul.com/img/og-image.png">

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/quickshul-icon.png">
    <meta name="theme-color" content="#0d1829">

    <!-- JSON-LD Structured Data -->
    @verbatim
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "SoftwareApplication",
          "@id": "https://quickshul.com/#software",
          "name": "QuickShul",
          "url": "https://quickshul.com",
          "applicationCategory": "BusinessApplication",
          "operatingSystem": "Web",
          "description": "Free, open-source synagogue management software. Member & family management, pledge tracking, email tools, Hebrew calendar, yahrtzeit reminders, and QuickBooks Online sync.",
          "featureList": [
            "Member and family management",
            "Pledge tracking and online payments via PayPal",
            "Email reminders and year-end giving statements via Gmail",
            "Hebrew calendar and yahrtzeit reminders",
            "QuickBooks Online integration",
            "Google Sign-In for members"
          ],
          "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever — no per-seat fees, no feature paywalls"
          },
          "isAccessibleForFree": true,
          "license": "https://github.com/ndavidovics/Quickshul",
          "author": {
            "@type": "Organization",
            "name": "QuickShul",
            "url": "https://quickshul.com"
          }
        },
        {
          "@type": "Organization",
          "@id": "https://quickshul.com/#organization",
          "name": "QuickShul",
          "url": "https://quickshul.com",
          "logo": {
            "@type": "ImageObject",
            "url": "https://quickshul.com/img/quickshul-icon.png"
          },
          "description": "QuickShul provides free, open-source synagogue management software for Jewish communities."
        },
        {
          "@type": "WebSite",
          "@id": "https://quickshul.com/#website",
          "url": "https://quickshul.com",
          "name": "QuickShul",
          "description": "Free synagogue management software",
          "publisher": { "@id": "https://quickshul.com/#organization" }
        }
      ]
    }
    </script>
    @endverbatim

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:        #0d1829;
            --navy-mid:    #1a2d5a;
            --navy-light:  #1e3570;
            --gold:        #c9a84c;
            --gold-bright: #e0be6a;
            --gold-dim:    #8c7035;
            --cream:       #f5f0e8;
            --cream-dim:   #c8c0ad;
            --white:       #ffffff;
            --border:      rgba(201, 168, 76, 0.18);
            --border-dim:  rgba(255, 255, 255, 0.07);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: var(--navy);
            color: var(--cream);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ── SVG pattern ───────────────────────────────────────── */
        .bg-pattern {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: 0.028;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='104'%3E%3Cpolygon points='30,2 58,17 58,47 30,62 2,47 2,17' fill='none' stroke='%23c9a84c' stroke-width='1'/%3E%3Cpolygon points='30,42 58,57 58,87 30,102 2,87 2,57' fill='none' stroke='%23c9a84c' stroke-width='1'/%3E%3C/svg%3E");
        }

        /* ── Navbar ─────────────────────────────────────────────── */
        .nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            padding: 0 max(2rem, 5vw);
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(13, 24, 41, 0.0);
            border-bottom: 1px solid transparent;
            transition: background 0.4s ease, border-color 0.4s ease, backdrop-filter 0.4s ease;
        }
        .nav.scrolled {
            background: rgba(13, 24, 41, 0.92);
            border-color: var(--border);
            backdrop-filter: blur(12px);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            text-decoration: none;
        }
        .nav-logo-mark {
            width: 34px; height: 34px;
            background: var(--gold);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .nav-logo-mark svg { width: 20px; height: 20px; }
        .nav-logo-name {
            font-family: 'DM Sans', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--white);
            letter-spacing: -0.01em;
        }
        /* "Quick" = gold, "Shul" = white */
        .nav-logo-name .logo-quick { color: var(--gold); }
        .nav-logo-name .logo-shul  { color: var(--white); }

        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .nav-link {
            color: var(--cream-dim);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            letter-spacing: 0.01em;
            transition: color 0.2s;
        }
        .nav-link:hover { color: var(--white); }

        .btn {
            display: inline-flex; align-items: center;
            padding: 0.6rem 1.4rem;
            border-radius: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-gold {
            background: var(--gold);
            color: var(--navy);
        }
        .btn-gold:hover {
            background: var(--gold-bright);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.35);
        }
        .btn-outline {
            background: transparent;
            color: var(--cream);
            border: 1.5px solid rgba(201, 168, 76, 0.45);
        }
        .btn-outline:hover {
            border-color: var(--gold);
            color: var(--gold);
        }
        .btn-lg {
            padding: 0.85rem 2rem;
            font-size: 1rem;
            border-radius: 8px;
        }

        /* ── Hero ───────────────────────────────────────────────── */
        .hero {
            position: relative;
            min-height: 100svh;
            display: flex;
            align-items: center;
            padding: 9rem max(2rem, 7vw) 6rem;
            overflow: hidden;
        }

        .hero-glow {
            position: absolute;
            top: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 900px;
            height: 600px;
            background: radial-gradient(ellipse at 50% 30%, rgba(26, 45, 90, 0.9) 0%, rgba(13, 24, 41, 0) 70%);
            pointer-events: none;
        }
        .hero-glow-gold {
            position: absolute;
            top: 20%;
            right: -5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(ellipse, rgba(201, 168, 76, 0.06) 0%, transparent 65%);
            pointer-events: none;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            max-width: 760px;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 1.5rem;
            opacity: 0;
            animation: fadeUp 0.7s 0.1s ease forwards;
        }
        .hero-eyebrow::before {
            content: '';
            display: block;
            width: 28px; height: 1px;
            background: var(--gold);
        }

        .hero-headline {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.6rem, 6vw, 4.2rem);
            font-weight: 900;
            line-height: 1.1;
            color: var(--white);
            letter-spacing: -0.02em;
            margin-bottom: 1.5rem;
            opacity: 0;
            animation: fadeUp 0.8s 0.2s ease forwards;
        }
        .hero-headline em {
            font-style: normal;
            color: var(--gold);
            position: relative;
        }
        .hero-headline em::after {
            content: '';
            position: absolute;
            bottom: 2px; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), transparent);
            opacity: 0.5;
        }

        .hero-sub {
            font-size: clamp(1rem, 2vw, 1.18rem);
            color: var(--cream-dim);
            line-height: 1.75;
            max-width: 580px;
            margin-bottom: 2.5rem;
            font-weight: 300;
            opacity: 0;
            animation: fadeUp 0.8s 0.35s ease forwards;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            opacity: 0;
            animation: fadeUp 0.8s 0.5s ease forwards;
        }

        .hero-scroll-indicator {
            position: absolute;
            bottom: 2.5rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            color: var(--cream-dim);
            font-size: 0.72rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            opacity: 0;
            animation: fadeIn 1s 1.2s ease forwards;
        }
        .scroll-mouse {
            width: 22px; height: 34px;
            border: 1.5px solid rgba(201,168,76,0.3);
            border-radius: 11px;
            display: flex;
            justify-content: center;
            padding-top: 5px;
        }
        .scroll-wheel {
            width: 3px; height: 7px;
            background: var(--gold);
            border-radius: 2px;
            animation: scrollWheel 1.8s ease-in-out infinite;
        }

        /* ── Section scaffold ───────────────────────────────────── */
        .section {
            position: relative;
            z-index: 1;
            padding: 6rem max(2rem, 7vw);
        }
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
            margin: 0 max(2rem, 7vw);
        }
        .section-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .section-label::before {
            content: '';
            width: 20px; height: 1px;
            background: var(--gold);
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
            letter-spacing: -0.015em;
            margin-bottom: 1rem;
        }
        .section-sub {
            font-size: 1.05rem;
            color: var(--cream-dim);
            max-width: 560px;
            font-weight: 300;
            line-height: 1.7;
        }

        /* ── Features ───────────────────────────────────────────── */
        .features-header {
            margin-bottom: 3.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5px;
            background: var(--border-dim);
            border: 1px solid var(--border-dim);
            border-radius: 12px;
            overflow: hidden;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.018);
            padding: 2rem 1.75rem;
            position: relative;
            transition: background 0.3s ease;
            cursor: default;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .feature-card:hover {
            background: rgba(201, 168, 76, 0.04);
        }
        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 42px; height: 42px;
            background: rgba(201, 168, 76, 0.1);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
            transition: background 0.3s;
        }
        .feature-card:hover .feature-icon {
            background: rgba(201, 168, 76, 0.18);
        }
        .feature-icon svg { width: 22px; height: 22px; color: var(--gold); }

        .feature-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.5rem;
        }
        .feature-desc {
            font-size: 0.88rem;
            color: var(--cream-dim);
            line-height: 1.7;
            font-weight: 300;
        }
        .feature-tag {
            display: inline-block;
            margin-top: 0.75rem;
            padding: 0.18rem 0.6rem;
            background: rgba(201, 168, 76, 0.12);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gold);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        /* ── How it works ───────────────────────────────────────── */
        .hiw-section {
            background: linear-gradient(180deg, var(--navy) 0%, rgba(26, 45, 90, 0.2) 50%, var(--navy) 100%);
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3rem;
            margin-top: 3.5rem;
            position: relative;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 2rem;
            left: calc(33.33% - 1.5rem);
            right: calc(33.33% - 1.5rem);
            height: 1px;
            background: linear-gradient(90deg, var(--gold-dim), var(--gold-dim));
            opacity: 0.4;
        }

        .step {
            display: flex;
            flex-direction: column;
        }
        .step-num {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--gold);
            opacity: 0.25;
            line-height: 1;
            margin-bottom: 1rem;
            letter-spacing: -0.04em;
        }
        .step-icon-wrap {
            width: 52px; height: 52px;
            background: rgba(201, 168, 76, 0.1);
            border: 1px solid var(--border);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.25rem;
        }
        .step-icon-wrap svg { width: 26px; height: 26px; color: var(--gold); }
        .step-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.6rem;
        }
        .step-desc {
            font-size: 0.9rem;
            color: var(--cream-dim);
            line-height: 1.75;
            font-weight: 300;
        }

        /* ── Pricing ────────────────────────────────────────────── */
        .pricing-section {
            text-align: center;
        }
        .pricing-card {
            max-width: 560px;
            margin: 3rem auto 0;
            background: linear-gradient(135deg, rgba(26, 45, 90, 0.6), rgba(30, 53, 112, 0.4));
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .pricing-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }
        .pricing-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(201, 168, 76, 0.12);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 0.3rem 0.9rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        .pricing-price {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--white);
            line-height: 1;
            margin-bottom: 0.3rem;
        }
        .pricing-sub {
            font-size: 0.92rem;
            color: var(--cream-dim);
            margin-bottom: 1.75rem;
            font-weight: 300;
        }
        .pricing-features {
            list-style: none;
            text-align: left;
            margin-bottom: 2rem;
        }
        .pricing-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            font-size: 0.93rem;
            color: var(--cream-dim);
            padding: 0.45rem 0;
            border-bottom: 1px solid var(--border-dim);
            font-weight: 300;
        }
        .pricing-features li:last-child { border-bottom: none; }
        .pricing-check {
            flex-shrink: 0;
            width: 18px; height: 18px;
            background: rgba(201, 168, 76, 0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-top: 1px;
        }
        .pricing-check svg { width: 10px; height: 10px; color: var(--gold); }

        .pricing-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ── CTA Banner ─────────────────────────────────────────── */
        .cta-section {
            text-align: center;
            padding: 7rem max(2rem, 7vw);
            position: relative;
            overflow: hidden;
        }
        .cta-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 50% 50%, rgba(201, 168, 76, 0.07) 0%, transparent 65%);
        }
        .cta-headline {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4.5vw, 3rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.25;
            margin-bottom: 1rem;
            position: relative;
        }
        .cta-sub {
            font-size: 1rem;
            color: var(--cream-dim);
            margin-bottom: 2.25rem;
            font-weight: 300;
            position: relative;
        }
        .cta-actions { position: relative; }

        /* ── Footer ─────────────────────────────────────────────── */
        footer {
            border-top: 1px solid var(--border-dim);
            padding: 2.5rem max(2rem, 7vw);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
            position: relative;
            z-index: 1;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .footer-brand-mark {
            width: 28px; height: 28px;
            border-radius: 5px;
            display: flex; align-items: center; justify-content: center;
        }
        .footer-brand-text {
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--white);
        }
        .footer-brand-text .logo-quick { color: var(--gold); }
        .footer-brand-text .logo-shul  { color: var(--white); }
        .footer-copy {
            font-size: 0.8rem;
            color: rgba(200, 192, 173, 0.5);
            font-weight: 300;
        }
        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
        }
        .footer-link {
            font-size: 0.82rem;
            color: rgba(200, 192, 173, 0.55);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-link:hover { color: var(--gold); }

        /* ── Animations ─────────────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes scrollWheel {
            0%   { transform: translateY(0); opacity: 1; }
            60%  { transform: translateY(10px); opacity: 0; }
            61%  { transform: translateY(0); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }
        .reveal-delay-5 { transition-delay: 0.5s; }

        /* ── Responsive ─────────────────────────────────────────── */
        @media (max-width: 900px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .steps { grid-template-columns: 1fr; gap: 2.5rem; }
            .steps::before { display: none; }
            .nav-link { display: none; }
        }
        @media (max-width: 600px) {
            .features-grid { grid-template-columns: 1fr; }
            .hero { padding: 7rem 1.5rem 4rem; }
            .section { padding: 4rem 1.5rem; }
            footer { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<div class="bg-pattern" aria-hidden="true"></div>

<!-- ── Navbar ──────────────────────────────────────────── -->
<nav class="nav" id="navbar">
    <a href="{{ route('home') }}" class="nav-logo">
        <div class="nav-logo-mark">
            <img src="/img/quickshul-icon.png" alt="QuickShul" style="width:32px;height:32px;border-radius:6px">
        </div>
        <span class="nav-logo-name"><span class="logo-quick">Quick</span><span class="logo-shul">Shul</span></span>
    </a>

    <div class="nav-right">
        <a href="#features" class="nav-link">Features</a>
        <a href="#how-it-works" class="nav-link">How It Works</a>
        <a href="{{ route('find-portal') }}" class="nav-link">Find Your Portal</a>
        <a href="{{ route('register') }}" class="btn btn-gold">Get Started Free</a>
    </div>
</nav>

<!-- ── Main Content ─────────────────────────────────────── -->
<main>

<!-- ── Hero ────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="hero-glow-gold" aria-hidden="true"></div>

    <div class="hero-inner">
        <div class="hero-eyebrow">Free &amp; Open Source</div>

        <h1 class="hero-headline">
            Modern member management<br>for your <em>shul</em>
        </h1>

        <p class="hero-sub">
            Already managing your shul's finances in QuickBooks? Your member portal is minutes away — import your members, start accepting payments, and send email reminders, all from one beautifully simple platform. Free, forever.
        </p>

        <div class="hero-actions">
            <a href="{{ route('register') }}" class="btn btn-gold btn-lg">Get Started Free</a>
            <a href="#how-it-works" class="btn btn-outline btn-lg">See How It Works</a>
        </div>
    </div>

    <div class="hero-scroll-indicator" aria-hidden="true">
        <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
        <span>Scroll</span>
    </div>
</section>

<!-- ── Features ─────────────────────────────────────────── -->
<div class="section-divider"></div>
<section class="section" id="features">
    <div class="features-header">
        <div class="section-label reveal">Everything your shul needs</div>
        <h2 class="section-title reveal reveal-delay-1">Built for the whole community</h2>
        <p class="section-sub reveal reveal-delay-2">A complete synagogue management platform. No per-seat fees, no paywalls — just the tools your community deserves.</p>
    </div>

    <div class="features-grid reveal reveal-delay-1">

        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="feature-title">Member &amp; Family Management</div>
            <div class="feature-desc">Maintain complete family records, contact details, Hebrew names, membership types, and birthdays — all in one place.</div>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
            </div>
            <div class="feature-title">Pledge Tracking &amp; Payments</div>
            <div class="feature-desc">Track open pledges and donations. Let families pay securely via PayPal — each shul connects their own merchant account.</div>
            <span class="feature-tag">PayPal</span>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <div class="feature-title">Email Tools</div>
            <div class="feature-desc">Send personalized balance reminders and year-end giving statements — delivered via your shul's own Gmail or Google Workspace account.</div>
            <span class="feature-tag">Gmail API</span>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="feature-title">Calendar &amp; Yahrtzeit Reminders</div>
            <div class="feature-desc">Minyan schedules, holiday overrides, and automatic annual yahrtzeit reminders in both Hebrew and Gregorian dates.</div>
            <span class="feature-tag">Hebcal</span>
        </div>

        <div class="feature-card" style="border-color:rgba(201,168,76,0.35);background:rgba(201,168,76,0.04)">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
            <div class="feature-title">QuickBooks Integration</div>
            <div class="feature-desc">Already in QuickBooks? Import your member balances in one click. Payments made in the portal sync back automatically — your books stay up to date.</div>
            <span class="feature-tag" style="background:rgba(201,168,76,0.15);color:#c9a84c;border-color:rgba(201,168,76,0.3)">QuickBooks Online</span>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4l3 3"/>
                </svg>
            </div>
            <div class="feature-title">Google Sign-In</div>
            <div class="feature-desc">Members log in with their Google account — no new passwords to remember. Secure, fast, and familiar for everyone.</div>
            <span class="feature-tag">Google OAuth</span>
        </div>

    </div>
</section>

<!-- ── QuickBooks Callout ─────────────────────────────────── -->
<div class="section-divider"></div>
<section class="section" style="padding-top:3rem;padding-bottom:3rem">
    <div class="reveal" style="
        background: linear-gradient(135deg, rgba(201,168,76,0.08) 0%, rgba(26,45,90,0.6) 100%);
        border: 1px solid rgba(201,168,76,0.25);
        border-radius: 16px;
        padding: 3rem 3.5rem;
        display: flex;
        align-items: center;
        gap: 3rem;
        flex-wrap: wrap;
    ">
        <div style="flex:1;min-width:260px">
            <div style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gold);margin-bottom:0.75rem">Already using QuickBooks?</div>
            <h3 style="font-family:'Playfair Display',serif;font-size:1.9rem;font-weight:700;color:var(--white);line-height:1.2;margin-bottom:1rem">
                Your member portal is<br><em style="color:var(--gold);font-style:normal">minutes away.</em>
            </h3>
            <p style="color:var(--cream-dim);font-size:1rem;line-height:1.7;margin:0">
                If your members are already in QuickBooks Online, you don't need to start from scratch. Connect your account, import your customer list, and QuickShul becomes your members' window into their balance — where they can view statements and pay online.
            </p>
        </div>
        <div style="display:flex;flex-direction:column;gap:1rem;min-width:220px">
            <div style="display:flex;align-items:center;gap:0.75rem;color:var(--cream);font-size:0.9rem">
                <span style="width:28px;height:28px;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a84c" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                Import members from QuickBooks
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;color:var(--cream);font-size:0.9rem">
                <span style="width:28px;height:28px;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a84c" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                Payments sync back automatically
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;color:var(--cream);font-size:0.9rem">
                <span style="width:28px;height:28px;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a84c" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                Resolve conflicts with one click
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;color:var(--cream);font-size:0.9rem">
                <span style="width:28px;height:28px;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a84c" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                No QuickBooks? Works without it too
            </div>
            <a href="{{ route('register') }}" class="btn btn-gold" style="margin-top:0.5rem;text-align:center">Get Started Free &rarr;</a>
        </div>
    </div>
</section>

<!-- ── How It Works ──────────────────────────────────────── -->
<div class="section-divider"></div>
<section class="section hiw-section" id="how-it-works">
    <div class="section-label reveal">Simple setup</div>
    <h2 class="section-title reveal reveal-delay-1">Up and running in minutes</h2>
    <p class="section-sub reveal reveal-delay-2">No IT department required. QuickShul is designed for treasurers and administrators — not developers.</p>

    <div class="steps">
        <div class="step reveal">
            <div class="step-num">01</div>
            <div class="step-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div class="step-title">Register your shul</div>
            <div class="step-desc">Enter your organization name, choose a subdomain (e.g. <em>bethel.quickshul.com</em>), and create your admin account. Done in under two minutes.</div>
        </div>
        <div class="step reveal reveal-delay-2">
            <div class="step-num">02</div>
            <div class="step-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                </svg>
            </div>
            <div class="step-title">Connect Gmail &amp; PayPal</div>
            <div class="step-desc">Authorize your shul's Gmail or Google Workspace account for sending emails, and link your PayPal merchant account for online payments.</div>
        </div>
        <div class="step reveal reveal-delay-3">
            <div class="step-num">03</div>
            <div class="step-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="step-title">Import &amp; go live</div>
            <div class="step-desc">Already in QuickBooks? Import your member list in one click. Or add families manually. Members immediately get access to pay pledges, view statements, and update their info.</div>
        </div>
    </div>
</section>

<!-- ── Pricing ───────────────────────────────────────────── -->
<div class="section-divider"></div>
<section class="section pricing-section">
    <div class="section-label reveal" style="justify-content:center">Pricing</div>
    <h2 class="section-title reveal reveal-delay-1">No surprises. No subscriptions.</h2>
    <p class="section-sub reveal reveal-delay-2" style="margin:0 auto">
        QuickShul is free software. Every shul deserves great tools, regardless of size or budget.
    </p>

    <div class="pricing-card reveal reveal-delay-2">
        <div class="pricing-badge">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
            Free, Forever
        </div>
        <div class="pricing-price">$0</div>
        <div class="pricing-sub">No per-seat fees. No feature paywalls. No credit card required.</div>

        <ul class="pricing-features">
            <li>
                <div class="pricing-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                Unlimited families &amp; members
            </li>
            <li>
                <div class="pricing-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                All features included — email tools, calendar, QB sync
            </li>
            <li>
                <div class="pricing-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                Hosted free at <em>yourshul.quickshul.com</em> — or self-host
            </li>
            <li>
                <div class="pricing-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                Open source — inspect, extend, or fork it
            </li>
            <li>
                <div class="pricing-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                Your data stays yours — no lock-in
            </li>
        </ul>

        <div class="pricing-actions">
            <a href="{{ route('register') }}" class="btn btn-gold btn-lg">Get Started Free</a>
            <a href="https://github.com/ndavidovics/Quickshul" target="_blank" class="btn btn-outline btn-lg">View on GitHub</a>
        </div>
    </div>
</section>

<!-- ── CTA Banner ────────────────────────────────────────── -->
<div class="section-divider"></div>
<section class="cta-section">
    <h2 class="cta-headline reveal">
        Ready to bring your shul<br>into the modern era?
    </h2>
    <p class="cta-sub reveal reveal-delay-1">If your shul is in QuickBooks, you're already halfway there. Set up takes less than 5 minutes.</p>
    <div class="cta-actions reveal reveal-delay-2">
        <a href="{{ route('register') }}" class="btn btn-gold btn-lg">Get Started Free &rarr;</a>
    </div>
</section>

</main><!-- end main -->

<!-- ── Footer ────────────────────────────────────────────── -->
<footer>
    <div>
        <a href="{{ route('home') }}" class="footer-brand">
            <div class="footer-brand-mark">
                <img src="/img/quickshul-icon.png" alt="QuickShul" style="width:24px;height:24px;border-radius:4px">
            </div>
            <span class="footer-brand-text"><span class="logo-quick">Quick</span><span class="logo-shul">Shul</span></span>
        </a>
        <p class="footer-copy" style="margin-top:0.4rem">Built with ♥ for Jewish communities</p>
        <p class="footer-copy" style="margin-top:0.25rem">Contact: <a href="mailto:noam@nanovix.com" style="color:inherit;text-decoration:underline">Noam Davidovics</a> &mdash; Memphis, TN</p>
    </div>

    <nav class="footer-links" aria-label="Footer navigation">
        <a href="{{ route('register') }}" class="footer-link">Get Started</a>
        <a href="{{ route('find-portal') }}" class="footer-link">Find Your Portal</a>
        <a href="https://github.com/ndavidovics/Quickshul" target="_blank" class="footer-link">GitHub</a>
        <a href="/privacy" class="footer-link">Privacy Policy</a>
        <a href="/agreement" class="footer-link">Terms of Use</a>
    </nav>
</footer>

<script>
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });

    // Scroll-triggered reveal
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3B46F2">
    <title>Routerverse - Homepage</title>

    <link rel="icon" type="image/png" href="{{ asset('image/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;600&family=Geist:wght@500;600;700;800&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        canvas: '#F7F8FC',
                        ink: '#0E1014',
                        muted: '#6C7082',
                        line: '#E6E8F0',
                        indigo: {
                            DEFAULT: '#3B46F2',
                            deep: '#2A35DC',
                            dark: '#1A20A0',
                        },
                        amber: { DEFAULT: '#D98A3D', light: '#F3B873' },
                        teal: { DEFAULT: '#00C2A8' },
                        rose: { DEFAULT: '#E6446A' },
                    },
                    fontFamily: {
                        display: ['"Geist"', 'sans-serif'],
                        body: ['"Inter"', 'sans-serif'],
                        mono: ['"Geist Mono"', 'monospace'],
                    },
                    fontSize: {
                        'body-base': ['17px', { lineHeight: '1.7', letterSpacing: '-0.01em' }],
                        'body-sm':   ['15px', { lineHeight: '1.65', letterSpacing: '-0.005em' }],
                        'body-xs':   ['13px', { lineHeight: '1.5' }],
                        'display-xl':['52px', { lineHeight: '1.1', letterSpacing: '-0.03em' }],
                        'display-lg':['40px', { lineHeight: '1.15', letterSpacing: '-0.025em' }],
                        'display-md':['28px', { lineHeight: '1.25', letterSpacing: '-0.02em' }],
                    },
                    boxShadow: {
                        soft: '0 2px 20px -4px rgba(14,16,20,0.08)',
                        card: '0 1px 2px rgba(14,16,20,0.04), 0 8px 24px -8px rgba(14,16,20,0.12)',
                        island: '0 8px 40px -8px rgba(14,16,20,0.45), 0 2px 8px rgba(14,16,20,0.30)',
                    },
                    borderRadius: {
                        '4xl': '2rem',
                        '5xl': '2.5rem',
                    },
                }
            }
        }
    </script>

    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #F7F8FC; color: #0E1014; -webkit-font-smoothing: antialiased; }
        ::selection { background: #3B46F2; color: #fff; }

        /* ─── DYNAMIC ISLAND NAVBAR ─── */
        #island-nav {
            position: fixed;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 4px;
            background: rgba(14,16,20,0.0);
            border: 1px solid rgba(255,255,255,0.0);
            border-radius: 9999px;
            padding: 6px 8px 6px 20px;
            box-shadow: none;
            transition: background 0.5s ease, border-color 0.5s ease, box-shadow 0.5s ease, backdrop-filter 0.5s ease;
            white-space: nowrap;
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
        }

        /* Transparan → teks hitam */
        #island-nav .island-logo span { color: #0E1014; transition: color 0.4s ease; }
        #island-nav .island-links a,
        #island-nav .island-links button {
            color: rgba(14,16,20,0.65);
            transition: color 0.3s ease, background 0.2s;
        }
        #island-nav .island-links a:hover,
        #island-nav .island-links button:hover {
            color: #0E1014;
            background: rgba(14,16,20,0.07);
        }
        #island-nav .island-links a.active { color: #0E1014; }
        #island-nav .island-cta .btn-ghost {
            color: rgba(14,16,20,0.65);
            border-color: rgba(14,16,20,0.20);
        }
        #island-nav .island-cta .btn-ghost:hover {
            color: #0E1014;
            border-color: rgba(14,16,20,0.40);
        }
        #island-nav .island-cta .btn-solid {
            color: #fff;
            background: #0E1014;
        }
        #island-nav .island-cta .btn-solid:hover { background: #2a2d35; }

        /* Scrolled → putih solid */
        #island-nav.scrolled {
            background: rgba(255,255,255,0.95);
            border-color: rgba(14,16,20,0.08);
            box-shadow: 0 8px 40px -8px rgba(14,16,20,0.18), 0 2px 12px rgba(14,16,20,0.10);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 6px 8px 6px 24px;
        }
        #island-nav.scrolled .island-logo span { color: #0E1014; }
        #island-nav.scrolled .island-logo img { filter: none; }
        #island-nav.scrolled .island-links a,
        #island-nav.scrolled .island-links button { color: rgba(14,16,20,0.60); }
        #island-nav.scrolled .island-links a:hover,
        #island-nav.scrolled .island-links button:hover {
            color: #0E1014;
            background: rgba(14,16,20,0.06);
        }
        #island-nav.scrolled .island-links a.active { color: #0E1014; }
        #island-nav.scrolled .island-cta .btn-ghost {
            color: rgba(14,16,20,0.60);
            border-color: rgba(14,16,20,0.18);
        }
        #island-nav.scrolled .island-cta .btn-ghost:hover {
            color: #0E1014;
            border-color: rgba(14,16,20,0.40);
        }
        #island-nav.scrolled .island-cta .btn-solid {
            color: #fff;
            background: #3B46F2;
        }
        #island-nav.scrolled .island-cta .btn-solid:hover { background: #2A35DC; }

        /* Logo */
        #island-nav .island-logo {
            display: flex; align-items: center; gap: 8px;
            flex-shrink: 0; text-decoration: none;
            margin-right: 4px;
        }
        #island-nav .island-logo img {
            height: 26px; width: auto; object-fit: contain;
            flex-shrink: 0; transition: filter 0.4s ease;
        }
        #island-nav .island-logo span {
            font-family: 'Geist', sans-serif; font-weight: 700;
            font-size: 15px; letter-spacing: -0.02em;
        }

        /* Links */
        #island-nav .island-links {
            display: flex; align-items: center; gap: 2px;
            margin: 0 8px 0 20px; overflow: visible; max-width: none;
            flex-shrink: 1;
        }
        #island-nav .island-links a,
        #island-nav .island-links button {
            font-family: 'Inter', sans-serif; font-size: 13.5px; font-weight: 500;
            text-decoration: none; padding: 7px 14px; border-radius: 9999px;
            border: none; background: transparent; cursor: pointer;
            display: flex; align-items: center; gap: 4px; letter-spacing: -0.01em;
        }

        /* CTA */
        #island-nav .island-cta {
            display: flex; align-items: center; gap: 6px;
            margin-left: 4px; flex-shrink: 0;
            white-space: nowrap;
        }
        #island-nav .island-cta .btn-ghost {
            font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500;
            padding: 8px 18px; border-radius: 9999px; border: 1px solid;
            background: transparent; cursor: pointer; transition: all 0.3s ease;
            text-decoration: none; white-space: nowrap; display: inline-flex;
            align-items: center; gap: 5px;
        }
        #island-nav .island-cta .btn-solid {
            font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600;
            padding: 8px 20px;
            border-radius: 9999px;
            border: none;
            cursor: pointer; transition: all 0.3s ease; text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;
        }

        /* Mobile button */
        #island-mobile-btn {
            display: none; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 9999px;
            background: rgba(14,16,20,0.08); border: none;
            color: #0E1014; font-size: 18px; cursor: pointer;
            margin-left: 8px; flex-shrink: 0; transition: background 0.2s, color 0.3s;
        }
        #island-mobile-btn:hover { background: rgba(14,16,20,0.14); }
        #island-nav.scrolled #island-mobile-btn { background: rgba(14,16,20,0.07); color: #0E1014; }

        /* ─── DROPDOWN (Paketan) ─── */
        .island-dropdown { position: relative; }
        .island-dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background: #1a1c22;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            padding: 6px;
            min-width: 160px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
            z-index: 100;
        }
        .island-dropdown-menu.open {
            opacity: 1;
            pointer-events: all;
            transform: translateX(-50%) translateY(0);
        }
        .island-dropdown-menu a {
            display: block !important;
            font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,0.7) !important;
            padding: 9px 14px !important; border-radius: 10px !important;
            text-decoration: none; transition: all 0.15s;
            background: transparent !important;
        }
        .island-dropdown-menu a:hover {
            background: rgba(255,255,255,0.08) !important;
            color: #fff !important;
        }

        /* Mobile panel */
        #island-mobile-panel {
            position: fixed; top: 72px; left: 50%; transform: translateX(-50%);
            z-index: 9998; width: calc(100vw - 32px); max-width: 380px;
            background: #0E1014; border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.10);
            box-shadow: 0 20px 60px rgba(14,16,20,0.5);
            padding: 12px; display: none;
        }
        #island-mobile-panel.open { display: block; }
        #island-mobile-panel a {
            display: block; font-size: 15px; font-weight: 500;
            color: rgba(255,255,255,0.75); padding: 12px 16px;
            border-radius: 12px; text-decoration: none; transition: all 0.15s; letter-spacing: -0.01em;
        }
        #island-mobile-panel a:hover { background: rgba(255,255,255,0.07); color: #fff; }
        #island-mobile-panel .panel-divider { height: 1px; background: rgba(255,255,255,0.08); margin: 8px 0; }
        #island-mobile-panel .panel-cta { display: flex; flex-direction: column; gap: 8px; padding: 8px 0 4px; }
        #island-mobile-panel .panel-cta a { text-align: center; border-radius: 12px; }
        #island-mobile-panel .panel-cta .p-ghost { border: 1px solid rgba(255,255,255,0.18); color: rgba(255,255,255,0.75); }
        #island-mobile-panel .panel-cta .p-solid { background: #3B46F2; color: #fff; font-weight: 600; }

        /* ─── BG Grid ─── */
        .bg-grid {
            background-image:
                linear-gradient(rgba(59,70,242,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,70,242,0.05) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* ─── HERO ANIMATED GLOW ─── */
        .hero-glow-wrap {
            pointer-events: none;
            position: absolute;
            inset: 0;
            overflow: hidden;
        }
        .hero-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            will-change: transform, opacity;
        }
        .hero-glow-1 {
            width: 700px; height: 500px;
            top: -160px; left: 50%; margin-left: -350px;
            background: radial-gradient(ellipse at center, rgba(59,70,242,0.45) 0%, transparent 70%);
            animation: glowDrift1 8s ease-in-out infinite alternate;
        }
        .hero-glow-2 {
            width: 420px; height: 320px;
            top: 60px; left: 58%;
            background: radial-gradient(ellipse at center, rgba(124,133,245,0.30) 0%, transparent 70%);
            animation: glowDrift2 10s ease-in-out infinite alternate;
        }
        .hero-glow-3 {
            width: 350px; height: 280px;
            top: 120px; left: 20%;
            background: radial-gradient(ellipse at center, rgba(0,194,168,0.18) 0%, transparent 70%);
            animation: glowDrift3 12s ease-in-out infinite alternate;
        }
        @keyframes glowDrift1 {
            0%   { transform: translate(0px, 0px) scale(1); opacity: 0.7; }
            50%  { transform: translate(30px, 20px) scale(1.08); opacity: 1; }
            100% { transform: translate(-20px, 30px) scale(0.95); opacity: 0.6; }
        }
        @keyframes glowDrift2 {
            0%   { transform: translate(0px, 0px) scale(1); opacity: 0.5; }
            50%  { transform: translate(-40px, 30px) scale(1.12); opacity: 0.8; }
            100% { transform: translate(20px, -20px) scale(0.9); opacity: 0.4; }
        }
        @keyframes glowDrift3 {
            0%   { transform: translate(0px, 0px) scale(1); opacity: 0.4; }
            50%  { transform: translate(30px, -20px) scale(1.15); opacity: 0.7; }
            100% { transform: translate(-15px, 25px) scale(0.92); opacity: 0.3; }
        }

        /* ─── Reveal ─── */
        .reveal { opacity: 0; transform: translateY(16px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.is-visible { opacity: 1; transform: translateY(0); }

        /* ─── Status dot ─── */
        .status-dot { position: relative; }
        .dot-ring {
            position: absolute; inset: -4px; border-radius: 9999px;
            border: 1px solid currentColor; opacity: .5;
            animation: pulse-ring 2.2s ease-out infinite;
        }
        @keyframes pulse-ring {
            0%   { transform: scale(0.7); opacity: .6; }
            80%  { transform: scale(1.9); opacity: 0; }
            100% { opacity: 0; }
        }

        .thin-scroll::-webkit-scrollbar { height: 4px; width: 4px; }
        .thin-scroll::-webkit-scrollbar-track { background: transparent; }
        .thin-scroll::-webkit-scrollbar-thumb { background: #D0D3E8; border-radius: 999px; }

        /* ─── Terminal / SSH Live Simulator ─── */
        #mt-terminal { scrollbar-width: thin; scrollbar-color: #2A3142 transparent; }
        #mt-terminal::-webkit-scrollbar { width: 5px; }
        #mt-terminal::-webkit-scrollbar-track { background: transparent; }
        #mt-terminal::-webkit-scrollbar-thumb { background: #2A3142; border-radius: 999px; }
        #mt-terminal .term-row { display: flex; flex-wrap: wrap; }
        #mt-terminal .term-prompt { color: #00C2A8; flex-shrink: 0; }
        #mt-terminal .term-cmd { color: #F4F6FB; margin-left: 2px; }
        #mt-terminal .term-output { color: #8B93A6; white-space: pre-wrap; word-break: break-word; opacity: 0; transform: translateY(2px); animation: termFadeIn .22s ease forwards; }
        #mt-terminal .term-output.term-head { color: #525C70; }
        #mt-terminal .term-cursor { display: inline-block; width: 6px; height: 13px; background: #00C2A8; margin-left: 4px; animation: termBlink 1s step-end infinite; vertical-align: -2px; }
        @keyframes termFadeIn { to { opacity: 1; transform: none; } }
        @keyframes termBlink { 50% { opacity: 0; } }
        @media (prefers-reduced-motion: reduce) {
            #mt-terminal .term-cursor { animation: none !important; }
            #mt-terminal .term-output { animation: none !important; opacity: 1 !important; transform: none !important; }
        }

        @media (max-width: 1024px) {
            #island-nav .island-links { display: none; }
            #island-mobile-btn { display: flex; }
            #island-nav { padding: 8px 8px 8px 18px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal { transition: none !important; opacity: 1 !important; transform: none !important; }
            .dot-ring { animation: none !important; }
            #island-nav { transition: none !important; }
            .hero-glow { animation: none !important; }
        }

        .font-display { font-family: 'Geist', sans-serif; }
        .font-body    { font-family: 'Inter', sans-serif; }
        .font-mono    { font-family: 'Geist Mono', monospace; }
        .card-lift { transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .card-lift:hover { transform: translateY(-3px); box-shadow: 0 12px 40px -12px rgba(14,16,20,0.18); }
        .section-label {
            display: inline-flex; align-items: center; gap: 6px;
            font-family: 'Geist Mono', monospace; font-size: 11.5px; font-weight: 500;
            letter-spacing: 0.06em; text-transform: uppercase;
            padding: 5px 12px; border-radius: 9999px;
        }
    </style>
</head>

<body class="font-body antialiased">

<!-- ═══════════════════════════════════════════
     DYNAMIC ISLAND NAVBAR
════════════════════════════════════════════ -->
<nav id="island-nav" role="navigation" aria-label="Navigasi utama">
    <!-- Logo -->
    <a href="#" class="island-logo">
        <img src="{{ asset('image/logo_doang.png') }}" alt="Routerverse Logo">
        <span>Routerverse</span>
    </a>

    <!-- Desktop links -->
    <div class="island-links">
        <a href="#" data-nav-link class="active">Beranda</a>
        <a href="#tentang-kami" data-nav-link>Tentang</a>
        <a href="#layanan-kami" data-nav-link>Layanan</a>

        <!-- Paketan dropdown -->
        <div class="island-dropdown">
            <button id="paketBtn" aria-haspopup="true" aria-expanded="false">
                Paketan <i class="bi bi-chevron-down" style="font-size:10px;transition:transform .2s" id="paketChevron"></i>
            </button>
            <div class="island-dropdown-menu" id="paketMenu" role="menu">
                <a href="#remote-server" role="menuitem">Remote Server</a>
                <a href="#hosting" role="menuitem">Hosting</a>
            </div>
        </div>

        <a href="{{ route('products.index') }}">Produk</a>
        <a href="#testimoni" data-nav-link>Testimoni</a>
        <a href="#alur-pemesanan" data-nav-link>Cara Pesan</a>
        <a href="#kontak-kami" data-nav-link>Kontak</a>
    </div>

    <!-- Auth CTA -->
    <div class="island-cta">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-solid">
                <i class="bi bi-grid-1x2" style="font-size:12px;"></i>
                <span>Dashboard</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="btn-solid">Masuk</a>
        @endauth
    </div>

    <!-- Mobile toggle -->
    <button id="island-mobile-btn" aria-label="Buka menu" aria-expanded="false">
        <i class="bi bi-list" id="mob-icon-open"></i>
        <i class="bi bi-x-lg hidden" id="mob-icon-close"></i>
    </button>
</nav>

<!-- Mobile Panel -->
<div id="island-mobile-panel" role="dialog" aria-label="Menu navigasi">
    <a href="#">Beranda</a>
    <a href="#tentang-kami">Tentang Kami</a>
    <a href="#layanan-kami">Layanan</a>
    <a href="#setting-jaringan">Setting Jaringan</a>
    <a href="#remote-server">Remote Server</a>
    <a href="#hosting">Hosting</a>
    <a href="{{ route('products.index') }}">Produk Kami</a>
    <a href="#testimoni">Testimoni</a>
    <a href="#alur-pemesanan">Cara Pesan</a>
    <a href="#kontak-kami">Kontak</a>
    <div class="panel-divider"></div>
    <div class="panel-cta">
        @auth
            <a href="{{ route('dashboard') }}" class="p-solid"><i class="bi bi-grid-1x2 mr-1"></i> Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="p-solid">Masuk</a>
        @endauth
    </div>
</div>


<!-- ═══════════════════════════════════════════
     HERO
════════════════════════════════════════════ -->
<section class="bg-grid relative overflow-hidden pt-36 pb-24 lg:pt-48 lg:pb-32">
    <!-- Animated glow blobs -->
    <div class="hero-glow-wrap" aria-hidden="true">
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>
        <div class="hero-glow hero-glow-3"></div>
    </div>

    <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-6 lg:grid-cols-2 lg:px-8">
        <div class="reveal text-center lg:text-left">
            <span class="status-dot mb-7 inline-flex items-center gap-2 rounded-full border border-line bg-white px-4 py-1.5 font-mono text-xs text-muted shadow-soft">
                <span class="relative inline-block h-1.5 w-1.5 rounded-full bg-teal text-teal">
                    <span class="dot-ring"></span>
                </span>
                routerverse / remote-access
            </span>

            <h1 class="font-display mt-2 text-[46px] font-bold leading-[1.08] tracking-[-0.03em] text-ink lg:text-[58px]">
                Kelola Server<br>
                <span style="background: linear-gradient(135deg,#3B46F2,#7C85F5); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Dari Mana Saja</span>
            </h1>

            <p class="mt-6 text-[17px] leading-[1.7] text-muted lg:max-w-md">
                Remote server aman &amp; efisien tanpa perlu hadir secara fisik.
                Infrastruktur Anda, kendali di tangan Anda.
            </p>

            <div class="mt-10 flex flex-wrap justify-center gap-3 lg:justify-start">
                <button class="group inline-flex items-center gap-2.5 rounded-full bg-indigo px-7 py-3.5 text-[15px] font-semibold text-white shadow-card transition-all hover:bg-indigo-deep hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo/40">
                    <i class="bi bi-person-plus"></i>
                    VPN Gratis Sekarang
                    <i class="bi bi-arrow-right text-sm transition-transform group-hover:translate-x-0.5"></i>
                </button>
                <a href="#layanan-kami" class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-7 py-3.5 text-[15px] font-semibold text-ink transition hover:border-indigo/30 hover:shadow-soft">
                    Lihat Layanan
                </a>
            </div>
        </div>

        <div class="reveal flex justify-center">
            <img src="{{ asset('image/datacenter.png') }}" alt="Ilustrasi datacenter" class="w-full max-w-[420px] drop-shadow-2xl">
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════
     TENTANG KAMI
════════════════════════════════════════════ -->
<section id="tentang-kami" class="py-24" style="background: linear-gradient(135deg,#1A20A0,#2A35DC,#3B46F2);">
    <div class="mx-auto grid max-w-6xl items-center gap-14 px-6 lg:grid-cols-2 lg:px-8">
        <div class="reveal order-2 flex justify-center lg:order-1">
            <img src="{{ asset('image/vector.png') }}" alt="Ilustrasi jaringan" class="w-full max-w-md opacity-90">
        </div>
        <div class="reveal order-1 lg:order-2">
            <span class="section-label border border-white/20 bg-white/10 text-white/70">
                <i class="bi bi-buildings"></i> Tentang Kami
            </span>
            <h2 class="font-display mt-5 text-[36px] font-bold leading-tight tracking-tight text-white lg:text-[44px]">
                Siapa Kami?
            </h2>
            <p class="mt-5 text-[16.5px] leading-[1.75] text-white/70">
                Kami menyediakan solusi remote server jarak jauh yang memudahkan Anda mengelola
                infrastruktur dari mana saja. Dengan layanan yang aman, cepat, dan efisien,
                pengelolaan server menjadi lebih fleksibel tanpa perlu hadir secara fisik di lokasi.
            </p>
            <div class="mt-8 grid grid-cols-3 gap-4">
                @foreach([['99.9%','Uptime'],['24/7','Dukungan'],['50+','Klien']] as $s)
                <div class="rounded-2xl border border-white/15 bg-white/[0.07] p-5 text-center">
                    <div class="font-display text-2xl font-bold text-white">{{ $s[0] }}</div>
                    <div class="mt-1 text-xs text-white/55">{{ $s[1] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════
     LAYANAN KAMI
════════════════════════════════════════════ -->
<section id="layanan-kami" class="bg-canvas py-24">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">
        <div class="reveal mx-auto mb-16 max-w-2xl text-center">
            <span class="section-label border border-line bg-white text-indigo shadow-soft">
                <i class="bi bi-stars"></i> Our Services
            </span>
            <h2 class="font-display mt-5 text-[34px] font-bold tracking-tight text-ink lg:text-[42px]">Layanan Kami</h2>
            <p class="mt-4 text-[16.5px] leading-[1.7] text-muted">
                Remote server yang aman dan efisien — didukung tim berpengalaman sehingga akses server Anda
                tetap <strong class="font-semibold text-ink">stabil, cepat, dan terlindungi</strong>.
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $services = [
                    ['icon'=>'bi-server','color'=>'#3B46F2','title'=>'Remote Server','desc'=>'Kelola server dari mana saja dengan koneksi aman dan efisien.'],
                    ['icon'=>'bi-hdd-network','color'=>'#00C2A8','title'=>'Monitoring 24/7','desc'=>'Pantau performa server real-time untuk menjaga kestabilan.'],
                    ['icon'=>'bi-cloud-check','color'=>'#D98A3D','title'=>'Backup & Restore','desc'=>'Backup otomatis dan pemulihan cepat untuk data penting Anda.'],
                    ['icon'=>'bi-shield-lock','color'=>'#E6446A','title'=>'Keamanan Berlapis','desc'=>'Perlindungan server dari ancaman siber dan akses tidak sah.'],
                ];
            @endphp
            @foreach($services as $s)
            <div class="card-lift reveal rounded-3xl border border-line bg-white p-7 shadow-card">
                <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl text-xl"
                     style="background:{{ $s['color'] }}1a; color:{{ $s['color'] }}">
                    <i class="{{ $s['icon'] }}"></i>
                </div>
                <h5 class="font-display text-[15px] font-semibold text-ink">{{ $s['title'] }}</h5>
                <p class="mt-2.5 text-[14px] leading-[1.65] text-muted">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════
     SETTING & KONFIGURASI JARINGAN
════════════════════════════════════════════ -->
<section id="setting-jaringan" class="py-24" style="background:linear-gradient(135deg,#1A20A0,#3B46F2);">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">
        <div class="reveal mx-auto mb-16 max-w-2xl text-center">
            <span class="section-label border border-white/20 bg-white/10 text-white/70">
                <i class="bi bi-router"></i> Network Configuration
            </span>
            <h2 class="font-display mt-5 text-[34px] font-bold tracking-tight text-white lg:text-[42px]">
                Setting &amp; Konfigurasi Jaringan
            </h2>
            <p class="mt-4 text-[16px] leading-[1.7] text-white/65">
                Dari rumahan, RT/RW Net, hingga perusahaan — tim kami menangani semua konfigurasi jaringan.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $nets = [
                    ['icon'=>'bi-diagram-3','color'=>'#7C85F5','title'=>'Load Balance','desc'=>'Bagi beban ISP otomatis agar koneksi tetap stabil saat trafik tinggi.'],
                    ['icon'=>'bi-plug','color'=>'#00C2A8','title'=>'PPPoE Server','desc'=>'Setup PPPoE untuk manajemen pelanggan bulanan dengan limit per user.'],
                    ['icon'=>'bi-wifi','color'=>'#F3B873','title'=>'Hotspot','desc'=>'Konfigurasi hotspot voucher dengan halaman login custom dan integrasi Mikhmon.'],
                    ['icon'=>'bi-signpost-split','color'=>'#E6446A','title'=>'Pisah Trafik','desc'=>'Pisah trafik game, streaming, dan browsing agar bandwidth efisien.'],
                ];
            @endphp
            @foreach($nets as $n)
            <div class="reveal rounded-2xl border border-white/10 bg-white/[0.05] p-7 transition hover:bg-white/[0.09]">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl text-2xl"
                     style="background:{{ $n['color'] }}22; color:{{ $n['color'] }}">
                    <i class="{{ $n['icon'] }}"></i>
                </div>
                <h5 class="font-display text-[15px] font-semibold text-white">{{ $n['title'] }}</h5>
                <p class="mt-2.5 text-[13.5px] leading-[1.65] text-white/60">{{ $n['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════
     BRAND PERANGKAT
════════════════════════════════════════════ -->
<section id="perangkat-jaringan" class="bg-white py-24">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">
        <div class="reveal mx-auto mb-14 max-w-xl text-center">
            <h2 class="font-display text-[30px] font-bold tracking-tight text-ink lg:text-[36px]">
                Mendukung Berbagai Merk Perangkat
            </h2>
            <p class="mt-4 text-[16px] leading-[1.7] text-muted">
                Berpengalaman menangani berbagai brand router, access point, dan switch.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7">
            @php
                $brands = [
                    ['name'=>'Mikrotik','desc'=>'Router & RouterOS','icon'=>'bi-hdd-network','logo'=>'mikrotik.png'],
                    ['name'=>'Ubiquiti','desc'=>'UniFi & airMAX','icon'=>'bi-broadcast','logo'=>'ubiquiti.png'],
                    ['name'=>'Ruijie','desc'=>'Switch & AP Enterprise','icon'=>'bi-diagram-2','logo'=>'ruijie.png'],
                    ['name'=>'Cisco','desc'=>'Switch & Router Enterprise','icon'=>'bi-router','logo'=>'cisco.png'],
                    ['name'=>'Aruba','desc'=>'Switch & AP','icon'=>'bi-wifi','logo'=>'aruba.png'],
                    ['name'=>'Huawei','desc'=>'Router & ONT Enterprise','icon'=>'bi-server','logo'=>'huawei.png'],
                    ['name'=>'C-Data','desc'=>'OLT & Router','icon'=>'bi-wifi','logo'=>'aruba.png'],
                    ['name'=>'Fortinet','desc'=>'Firewall','icon'=>'bi-wifi','logo'=>'fortinet.png'],
                    ['name'=>'HSGQ','desc'=>'OLT','icon'=>'bi-wifi','logo'=>'hsgq.png'],
                    ['name'=>'Juniper','desc'=>'Router & Switch Enterprise','icon'=>'bi-wifi','logo'=>'juniper.png'],
                    ['name'=>'VSOL','desc'=>'OLT & Router','icon'=>'bi-wifi','logo'=>'vsol.png'],
                    ['name'=>'ZTE','desc'=>'Router & Switch Enterprise','icon'=>'bi-wifi','logo'=>'juniper.png'],
                ];
            @endphp
            @foreach($brands as $b)
                @php $hasLogo = file_exists(public_path('image/brands/'.$b['logo'])); @endphp
                <div class="reveal card-lift flex flex-col items-center rounded-2xl border border-line bg-canvas p-5 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-white shadow-soft">
                        @if($hasLogo)
                            <img src="{{ asset('image/brands/'.$b['logo']) }}" alt="{{ $b['name'] }}" class="h-[68%] w-[68%] object-contain">
                        @else
                            <i class="bi {{ $b['icon'] }} text-xl text-indigo"></i>
                        @endif
                    </div>
                    <div class="text-[13px] font-semibold text-ink">{{ $b['name'] }}</div>
                    <div class="mt-0.5 text-[11.5px] text-muted">{{ $b['desc'] }}</div>
                </div>
            @endforeach
            <div class="reveal flex flex-col items-center justify-center rounded-2xl border border-dashed border-indigo/40 bg-indigo/5 p-5 text-center">
                <i class="bi bi-plus-circle mb-2 text-xl text-indigo"></i>
                <div class="text-[13px] font-semibold text-indigo">Dan Lainnya</div>
                <div class="mt-0.5 text-[11.5px] text-muted">Tanya kami</div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════
     PAKET REMOTE SERVER
════════════════════════════════════════════ -->
<section id="remote-server" class="py-24" style="background:linear-gradient(135deg,#1A20A0,#3B46F2);">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">
        <div class="reveal mx-auto mb-16 max-w-xl text-center">
            <span class="section-label border border-white/20 bg-white/10 text-white/70">
                <i class="bi bi-hdd-network"></i> Remote Server
            </span>
            <h2 class="font-display mt-5 text-[34px] font-bold tracking-tight text-white lg:text-[42px]">
                Paket Remote Server
            </h2>
            <p class="mt-4 text-[15.5px] leading-[1.7] text-white/60">Pilih paket terbaik sesuai kebutuhan Anda</p>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="reveal flex flex-col rounded-3xl border border-white/10 bg-white/[0.05] p-8">
                <h5 class="font-display text-[17px] font-semibold text-white">Starter</h5>
                <p class="mt-1 text-[13.5px] text-white/50">Cocok untuk uji coba</p>
                <div class="mt-5 font-mono text-[30px] font-semibold text-white">Rp 0
                    <span class="text-[13px] font-normal text-white/45">/ bulan</span>
                </div>
                <hr class="my-6 border-white/10">
                <ul class="flex-1 space-y-3 text-[14px] text-white/70">
                    @foreach(['1 Port','Akses Dasar Remote','L2TP/IPsec','Garansi'] as $f)
                    <li class="flex items-center gap-2.5"><i class="bi bi-check-circle-fill text-teal text-base"></i>{{ $f }}</li>
                    @endforeach
                </ul>
                <button type="button" onclick="openQrisWelcome('Starter Remote Server','Rp 0 / bulan')" class="mt-8 block rounded-full border border-white/20 py-3 text-center text-[14px] font-semibold text-white transition hover:bg-white/10 cursor-pointer">
                    <i class="bi bi-credit-card mr-1.5"></i> Pilih Pembayaran
                </button>
            </div>

            <div class="reveal relative flex flex-col rounded-3xl bg-white p-8 shadow-[0_24px_64px_-12px_rgba(14,16,20,0.4)] lg:-translate-y-4">
                <span class="absolute -top-3.5 left-8 rounded-full bg-amber px-4 py-1 font-mono text-[11px] font-semibold uppercase tracking-wide text-white">
                    Rekomendasi
                </span>
                <h5 class="font-display text-[17px] font-semibold text-ink">Basic</h5>
                <p class="mt-1 text-[13.5px] text-muted">Paling banyak dipilih</p>
                <div class="mt-5 font-mono text-[30px] font-semibold text-ink">Rp 2.000
                    <span class="text-[13px] font-normal text-muted">/ bulan</span>
                </div>
                <hr class="my-6 border-line">
                <ul class="flex-1 space-y-3 text-[14px] text-ink/75">
                    @foreach(['3 Port','Semua Fitur Starter','Monitoring Lanjutan','Dukungan Teknis 24/7'] as $f)
                    <li class="flex items-center gap-2.5"><i class="bi bi-check-circle-fill text-indigo text-base"></i>{{ $f }}</li>
                    @endforeach
                </ul>
                <button type="button" onclick="openQrisWelcome('Basic Remote Server','Rp 2.000 / bulan')" class="mt-8 block rounded-full bg-indigo py-3 text-center text-[14px] font-semibold text-white transition hover:bg-indigo-deep cursor-pointer">
                    <i class="bi bi-credit-card mr-1.5"></i> Pilih Pembayaran
                </button>
            </div>

            <div class="reveal flex flex-col rounded-3xl border border-white/10 bg-white/[0.05] p-8">
                <h5 class="font-display text-[17px] font-semibold text-white">Premium</h5>
                <p class="mt-1 text-[13.5px] text-white/50">Untuk kebutuhan besar</p>
                <div class="mt-5 font-mono text-[30px] font-semibold text-white">Rp 10.000
                    <span class="text-[13px] font-normal text-white/45">/ bulan</span>
                </div>
                <hr class="my-6 border-white/10">
                <ul class="flex-1 space-y-3 text-[14px] text-white/70">
                    @foreach(['5 Port','Semua Fitur Basic','Mikhmon Online 1 Bulan Gratis'] as $f)
                    <li class="flex items-center gap-2.5"><i class="bi bi-check-circle-fill text-teal text-base"></i>{{ $f }}</li>
                    @endforeach
                </ul>
                <button type="button" onclick="openQrisWelcome('Premium Remote Server','Rp 10.000 / bulan')" class="mt-8 block rounded-full border border-white/20 py-3 text-center text-[14px] font-semibold text-white transition hover:bg-white/10 cursor-pointer">
                    <i class="bi bi-credit-card mr-1.5"></i> Pilih Pembayaran
                </button>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════
     PAKET HOSTING
════════════════════════════════════════════ -->
<section id="hosting" class="bg-white py-24">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">
        <div class="reveal mx-auto mb-14 max-w-xl text-center">
            <span class="section-label border border-line bg-canvas text-indigo">
                <i class="bi bi-server"></i> Hosting
            </span>
            <h2 class="font-display mt-5 text-[34px] font-bold tracking-tight text-ink lg:text-[42px]">Paket Hosting</h2>
            <p class="mt-4 text-[16px] leading-[1.7] text-muted">Pilih paket terbaik sesuai kebutuhan Anda</p>
        </div>

        <div class="mx-auto grid max-w-4xl gap-5 sm:grid-cols-2">
            <div class="reveal relative flex flex-col rounded-3xl p-8" style="background:linear-gradient(135deg,#1A20A0,#2A35DC);">
                <span class="absolute -top-3.5 left-8 rounded-full bg-amber px-4 py-1 font-mono text-[11px] font-semibold uppercase tracking-wide text-white">Best Seller</span>
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10">
                    <img src="{{ asset('image/mikhmonwhite.PNG') }}" alt="Mikhmon" class="h-10 w-10 object-contain">
                </div>
                <h5 class="text-center font-display text-[17px] font-semibold text-white">Mikhmon Online</h5>
                <div class="mt-3 text-center font-mono text-[26px] font-semibold text-white">Rp 10.000
                    <span class="text-[13px] font-normal text-white/45">/ bulan</span>
                </div>
                <hr class="my-6 border-white/10">
                <ul class="flex-1 space-y-3 text-[14px] text-white/70">
                    @foreach(['Akses Mikhmon Online 24/7','Remote Dashboard dari Mana Saja','Manage Hotspot & Voucheran','Dapat VPN Port API'] as $f)
                    <li class="flex items-center gap-2.5"><i class="bi bi-check-circle-fill text-teal"></i>{{ $f }}</li>
                    @endforeach
                </ul>
                <button type="button" onclick="openQrisWelcome('Mikhmon Online','Rp 10.000 / bulan')" class="mt-8 block rounded-full bg-white py-3 text-center text-[14px] font-semibold text-indigo-dark transition hover:bg-white/90 cursor-pointer">
                    <i class="bi bi-credit-card mr-1.5"></i> Pilih Pembayaran
                </button>
            </div>

            <div class="reveal flex flex-col rounded-3xl p-8" style="background:linear-gradient(135deg,#1A20A0,#2A35DC);">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10">
                    <img src="{{ asset('image/phpnuxbill_square.png') }}" alt="PHPNuxBill" class="h-10 w-10 object-contain">
                </div>
                <h5 class="text-center font-display text-[17px] font-semibold text-white">PHPNuxBill</h5>
                <div class="mt-3 text-center font-mono text-[26px] font-semibold text-white">Rp 10.000
                    <span class="text-[13px] font-normal text-white/45">/ bulan</span>
                </div>
                <hr class="my-6 border-white/10">
                <ul class="flex-1 space-y-3 text-[14px] text-white/70">
                    @foreach(['Akses PHPNuxBill 24/7','Manage Hotspot & PPPoE Teratur','Monitoring Real-time','Dapat VPN Port API'] as $f)
                    <li class="flex items-center gap-2.5"><i class="bi bi-check-circle-fill text-teal"></i>{{ $f }}</li>
                    @endforeach
                </ul>
                <button type="button" onclick="openQrisWelcome('PHPNuxBill','Rp 10.000 / bulan')" class="mt-8 block rounded-full bg-white py-3 text-center text-[14px] font-semibold text-indigo-dark transition hover:bg-white/90 cursor-pointer">
                    <i class="bi bi-credit-card mr-1.5"></i> Pilih Pembayaran
                </button>
            </div>
        </div>

        <!-- Terminal / SSH Live Simulator -->
        <div class="reveal mt-20">
            <div class="mx-auto mb-10 max-w-xl text-center">
                <span class="section-label border border-line bg-canvas text-indigo">
                    <i class="bi bi-terminal"></i> Live Simulator
                </span>
                <h4 class="font-display mt-5 text-[26px] font-bold tracking-tight text-ink">Akses Terminal Mikrotik Secara Real-time</h4>
                <p class="mt-3 text-[14.5px] leading-[1.7] text-muted">
                    Login SSH ke router dan jalankan command RouterOS langsung dari dashboard — secepat dan senyata ini.
                </p>
            </div>

            <div class="mx-auto max-w-3xl overflow-hidden rounded-3xl border border-line shadow-card">
                <div class="flex items-center gap-3 border-b border-line bg-canvas px-5 py-3.5">
                    <div class="flex gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background:#FF5F57"></span>
                        <span class="h-2.5 w-2.5 rounded-full" style="background:#FFBC2E"></span>
                        <span class="h-2.5 w-2.5 rounded-full" style="background:#28C840"></span>
                    </div>
                    <div class="flex items-center gap-1.5 rounded-full bg-white px-3 py-1 font-mono text-xs text-muted border border-line">
                        <i class="bi bi-hdd-network text-[10px]"></i> ssh admin@192.168.88.1
                    </div>
                    <span class="status-dot ml-auto hidden items-center gap-1.5 rounded-full bg-teal/10 px-3 py-1 font-mono text-[11px] font-semibold text-teal sm:inline-flex">
                        <span class="relative inline-block h-1.5 w-1.5 rounded-full bg-teal"><span class="dot-ring"></span></span>Connected
                    </span>
                </div>

                <div id="mt-terminal" class="thin-scroll h-[320px] overflow-y-auto bg-[#0B0E14] px-5 py-4 font-mono text-[12.5px] leading-[1.85] sm:h-[380px]"></div>
            </div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════
     PRODUK / TEMPLATE
════════════════════════════════════════════ -->
@if(isset($produkList) && $produkList->count())
<section id="produk-kami" class="bg-canvas py-24">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">
        <div class="reveal mx-auto mb-14 max-w-xl text-center">
            <span class="section-label border border-line bg-white text-indigo shadow-soft">
                <i class="bi bi-grid"></i> Produk
            </span>
            <h2 class="font-display mt-5 text-[34px] font-bold tracking-tight text-ink lg:text-[42px]">Template &amp; Produk</h2>
            <p class="mt-4 text-[16px] leading-[1.7] text-muted">Template siap pakai untuk berbagai kebutuhan jaringan Anda.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($produkList->take(8) as $produk)
            <a href="{{ route('products.show', $produk->id) }}" class="reveal card-lift group block overflow-hidden rounded-3xl border border-line bg-white shadow-card">
                @if($produk->image)
                <div class="aspect-video overflow-hidden bg-canvas">
                    <img src="{{ asset('storage/'.$produk->image) }}" alt="{{ $produk->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.03]">
                </div>
                @endif
                <div class="p-5">
                    <span class="inline-block rounded-lg bg-indigo/10 px-2.5 py-1 font-mono text-[11px] font-semibold text-indigo">{{ $produk->category ?? 'Template' }}</span>
                    <h6 class="font-display mt-2.5 text-[14.5px] font-semibold text-ink">{{ $produk->name }}</h6>
                    <div class="mt-2 font-mono text-[15px] font-semibold text-ink">Rp {{ number_format($produk->price, 0, ',', '.') }}</div>
                </div>
            </a>
            @endforeach
        </div>
        <div class="reveal mt-12 text-center">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo px-7 py-3.5 text-[14.5px] font-semibold text-white shadow-card transition hover:bg-indigo-deep">
                Lihat Semua Template <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════
     TESTIMONI
════════════════════════════════════════════ --}}
@include('components.testimoni')


{{-- ═══════════════════════════════════════════
     ALUR PEMESANAN
════════════════════════════════════════════ --}}
@include('components.alur-pemesanan')


<!-- ═══════════════════════════════════════════
     KONTAK KAMI
════════════════════════════════════════════ -->
<section id="kontak-kami" class="py-24" style="background:linear-gradient(135deg,#1A20A0,#3B46F2);">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">
        <div class="reveal mx-auto mb-16 max-w-xl text-center">
            <span class="section-label border border-white/20 bg-white/10 text-white/70">
                <i class="bi bi-chat-dots"></i> Get In Touch
            </span>
            <h2 class="font-display mt-5 text-[34px] font-bold tracking-tight text-white lg:text-[42px]">Hubungi Kami</h2>
            <p class="mt-4 text-[16px] leading-[1.7] text-white/60">
                Pertanyaan layanan, konsultasi, atau bantuan teknis? Tim kami siap membantu.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="https://instagram.com/routerverse.id" target="_blank"
               class="reveal rounded-3xl border border-white/10 bg-white/[0.05] p-8 text-center transition hover:bg-white/[0.10]">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl text-xl text-white"
                     style="background:linear-gradient(135deg,#f58529,#dd2a7b,#8134af)">
                    <i class="bi bi-instagram"></i>
                </div>
                <h5 class="font-display font-semibold text-white">Instagram</h5>
                <p class="mt-1.5 text-[13.5px] text-white/55">Update layanan, tips jaringan &amp; promo</p>
                <span class="mt-3 inline-block font-mono text-xs text-white/50">@routerverse.id</span>
            </a>

            <a href="https://wa.me/6285173484715?text=Halo%20Routerverse%2C%20saya%20mau%20bertanya" target="_blank"
               class="reveal rounded-3xl border border-white/10 bg-white/[0.05] p-8 text-center transition hover:bg-white/[0.10]">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#25D366] text-xl text-white">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <h5 class="font-display font-semibold text-white">WhatsApp</h5>
                <p class="mt-1.5 text-[13.5px] text-white/55">Chat langsung untuk konsultasi &amp; bantuan</p>
                <span class="mt-3 inline-block font-mono text-xs text-white/50">+62 851-7384-4715</span>
            </a>

            <a href="https://routerverse.id" target="_blank"
               class="reveal rounded-3xl border border-white/10 bg-white/[0.05] p-8 text-center transition hover:bg-white/[0.10]">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-xl text-indigo">
                    <i class="bi bi-globe2"></i>
                </div>
                <h5 class="font-display font-semibold text-white">Website</h5>
                <p class="mt-1.5 text-[13.5px] text-white/55">Info lengkap layanan, paket &amp; status server</p>
                <span class="mt-3 inline-block font-mono text-xs text-white/50">routerverse.id</span>
            </a>
        </div>

        <div class="reveal mt-12 text-center">
            <a href="https://wa.me/6285173484715?text=Halo%20Routerverse%2C%20saya%20mau%20konsultasi" target="_blank"
               class="inline-flex items-center gap-2.5 rounded-full bg-white px-8 py-4 text-[15px] font-semibold text-indigo-dark shadow-card transition hover:bg-white/90">
                <i class="bi bi-headset"></i> Konsultasi Gratis Sekarang
            </a>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════
     FOOTER
════════════════════════════════════════════ -->
<footer class="bg-ink py-16 text-white/60">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[2fr_1fr_1fr_1.3fr]">
            <div>
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('image/logo_doang.png') }}" alt="Routerverse" class="h-8 w-auto">
                    <span class="font-display text-[16px] font-semibold text-white">Routerverse</span>
                </div>
                <p class="mt-4 max-w-xs text-[14px] leading-[1.7] text-white/45">
                    Layanan remote jaringan, server management, dan hosting untuk bisnis, RT/RW Net, &amp; perusahaan.
                </p>
                <div class="mt-5 flex gap-3">
                    @foreach([['bi-instagram','https://instagram.com/routerverse.id','Instagram'],['bi-whatsapp','https://wa.me/6285173484715','WhatsApp'],['bi-globe2','https://routerverse.id','Website']] as $soc)
                    <a href="{{ $soc[1] }}" target="_blank" aria-label="{{ $soc[2] }}"
                       class="flex h-9 w-9 items-center justify-center rounded-full border border-white/15 transition hover:border-white/40 hover:text-white">
                        <i class="bi {{ $soc[0] }}"></i>
                    </a>
                    @endforeach
                </div>
            </div>

            <div>
                <h6 class="font-mono text-[11px] uppercase tracking-wider text-white/35">Menu</h6>
                <ul class="mt-4 space-y-2.5 text-[14px]">
                    @foreach([['#tentang-kami','Tentang Kami'],['#layanan-kami','Layanan Kami'],['#kontak-kami','Kontak']] as $m)
                    <li><a href="{{ $m[0] }}" class="transition hover:text-white">{{ $m[1] }}</a></li>
                    @endforeach
                    <li><a href="{{ route('products.index') }}" class="transition hover:text-white">Produk Kami</a></li>
                </ul>
            </div>

            <div>
                <h6 class="font-mono text-[11px] uppercase tracking-wider text-white/35">Layanan</h6>
                <ul class="mt-4 space-y-2.5 text-[14px]">
                    @foreach([['#remote-server','Remote Server'],['#hosting','Hosting'],['#setting-jaringan','Setting Jaringan'],['https://kuma.routerverse.id/status/routerverse','Status Server']] as $l)
                    <li><a href="{{ $l[0] }}" class="transition hover:text-white">{{ $l[1] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h6 class="font-mono text-[11px] uppercase tracking-wider text-white/35">Hubungi Kami</h6>
                <ul class="mt-4 space-y-3 text-[14px]">
                    <li class="flex items-start gap-2"><i class="bi bi-geo-alt mt-0.5"></i> Dayeuhkolot, Kab. Bandung</li>
                    <li class="flex items-start gap-2"><i class="bi bi-whatsapp mt-0.5"></i>
                        <a href="https://wa.me/6285173484715" target="_blank" class="transition hover:text-white">+62 851-7384-4715</a>
                    </li>
                    <li class="flex items-start gap-2"><i class="bi bi-envelope mt-0.5"></i>
                        <a href="mailto:iqbalrinaldi098@gmail.com" class="transition hover:text-white">iqbalrinaldi098@gmail.com</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12 border-t border-white/10 pt-6 text-center text-[12.5px] text-white/30">
            © {{ date('Y') }} Routerverse. All Rights Reserved.
        </div>
    </div>
</footer>

<!-- Floating WhatsApp -->
<a href="https://wa.me/6285173484715?text=Halo%20saya%20tertarik%20dengan%20layanan%20Anda!" target="_blank"
   aria-label="Chat WhatsApp"
   class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full text-2xl text-white shadow-card transition hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366]/50"
   style="background:#25D366">
    <i class="bi bi-whatsapp"></i>
</a>

<div class="pointer-events-none fixed inset-x-0 bottom-3 z-40 text-center font-mono text-[11px] text-ink/25">
    Made with ❤️ by <span class="font-semibold text-indigo/50">Routerverse</span>
</div>


<script>
(function () {
    /* ── Scroll: transparan → putih ── */
    var nav = document.getElementById('island-nav');
    function updateScroll() {
        if (window.scrollY > 40) nav.classList.add('scrolled');
        else nav.classList.remove('scrolled');
    }
    updateScroll();
    window.addEventListener('scroll', updateScroll, { passive: true });

    /* ── Mobile panel ── */
    var mobileBtn   = document.getElementById('island-mobile-btn');
    var mobilePanel = document.getElementById('island-mobile-panel');
    var iconOpen    = document.getElementById('mob-icon-open');
    var iconClose   = document.getElementById('mob-icon-close');

    function closeMobile() {
        mobilePanel.classList.remove('open');
        iconOpen.classList.remove('hidden');
        iconClose.classList.add('hidden');
        mobileBtn.setAttribute('aria-expanded', 'false');
    }

    mobileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = mobilePanel.classList.toggle('open');
        iconOpen.classList.toggle('hidden', isOpen);
        iconClose.classList.toggle('hidden', !isOpen);
        mobileBtn.setAttribute('aria-expanded', isOpen);
    });

    mobilePanel.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', closeMobile);
    });

    document.addEventListener('click', function (e) {
        if (!nav.contains(e.target) && !mobilePanel.contains(e.target)) closeMobile();
    });

    /* ── Paketan dropdown ── */
    var paketBtn     = document.getElementById('paketBtn');
    var paketMenu    = document.getElementById('paketMenu');
    var paketChevron = document.getElementById('paketChevron');

    function closeDropdown() {
        paketMenu.classList.remove('open');
        paketChevron.style.transform = '';
        paketBtn.setAttribute('aria-expanded', 'false');
    }

    paketBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = paketMenu.classList.toggle('open');
        paketChevron.style.transform = isOpen ? 'rotate(180deg)' : '';
        paketBtn.setAttribute('aria-expanded', isOpen);
    });

    /* Tutup dropdown saat klik link di dalamnya */
    paketMenu.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', closeDropdown);
    });

    /* Tutup dropdown saat klik di luar */
    document.addEventListener('click', function (e) {
        if (!paketBtn.closest('.island-dropdown').contains(e.target)) closeDropdown();
    });

    /* Tutup dropdown saat ESC */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeDropdown(); closeMobile(); }
    });

    /* ── Scrollspy ── */
    var navLinks = document.querySelectorAll('[data-nav-link]');
    var sections = document.querySelectorAll('section[id]');
    function scrollSpy() {
        var current = '';
        sections.forEach(function (sec) {
            if (window.scrollY >= sec.offsetTop - 100) current = sec.id;
        });
        navLinks.forEach(function (link) {
            var href = link.getAttribute('href');
            link.classList.toggle('active', href === '#' + current || (current === '' && href === '#'));
        });
    }
    window.addEventListener('scroll', scrollSpy, { passive: true });

    /* ── Reveal on scroll ── */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
    } else {
        document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('is-visible'); });
    }

})();
</script>

<script>
/* ── Terminal / SSH Live Simulator ── */
(function () {
    var term = document.getElementById('mt-terminal');
    if (!term) return;

    var PROMPT = '[admin@MikroTik] > ';
    var commands = [
        {
            cmd: '/ip address print',
            lines: [
                'Flags: X - disabled, I - invalid, D - dynamic',
                ' #    ADDRESS             NETWORK          INTERFACE',
                ' 0    192.168.88.1/24     192.168.88.0     bridge1',
                ' 1    10.10.10.1/24       10.10.10.0       pppoe-out1',
                ' 2    103.21.55.4/29      103.21.55.0      ether1'
            ]
        },
        {
            cmd: '/ppp active print',
            lines: [
                'Flags:',
                ' #    NAME        SERVICE   ADDRESS         UPTIME',
                ' 0    client001   pppoe     10.10.10.2      02:14:33',
                ' 1    client002   pppoe     10.10.10.3      01:02:10',
                ' 2    client003   pppoe     10.10.10.4      00:45:02'
            ]
        },
        {
            cmd: '/interface print',
            lines: [
                'Flags: D - dynamic, X - disabled, R - running',
                ' #      NAME           TYPE      ACTUAL-MTU',
                ' 0   R  ether1         ether     1500',
                ' 1   R  bridge1        bridge    1500',
                ' 2   R  pppoe-out1     pppoe     1492'
            ]
        },
        {
            cmd: '/system resource print',
            lines: [
                '      uptime: 21d04h12m33s',
                '     version: 7.15.3 (stable)',
                '    cpu-load: 3%',
                ' free-memory: 812.4MiB',
                'total-memory: 1024.0MiB'
            ]
        },
        {
            cmd: '/queue simple print',
            lines: [
                'Flags: X - disabled, I - invalid, D - dynamic',
                ' 0   name="client001" target=10.10.10.2/32 max-limit=10M/10M',
                ' 1   name="client002" target=10.10.10.3/32 max-limit=20M/20M'
            ]
        }
    ];

    var ci = 0, started = false;
    var TYPE_SPEED = 28, LINE_DELAY = 170, HOLD = 1600;

    function scrollDown() { term.scrollTop = term.scrollHeight; }

    function newRow() {
        var row = document.createElement('div');
        row.className = 'term-row';
        var p = document.createElement('span');
        p.className = 'term-prompt';
        p.textContent = PROMPT;
        var c = document.createElement('span');
        c.className = 'term-cmd';
        var cur = document.createElement('span');
        cur.className = 'term-cursor';
        row.appendChild(p); row.appendChild(c); row.appendChild(cur);
        term.appendChild(row);
        scrollDown();
        return { cmdEl: c, cursorEl: cur };
    }

    function addOutputLine(text, idx) {
        var div = document.createElement('div');
        div.className = 'term-output' + (idx === 0 ? ' term-head' : '');
        div.textContent = text;
        term.appendChild(div);
        scrollDown();
    }

    function typeCommand() {
        var entry = commands[ci % commands.length];
        var row = newRow();
        var i = 0;
        (function typeChar() {
            if (i < entry.cmd.length) {
                row.cmdEl.textContent += entry.cmd.charAt(i);
                i++;
                scrollDown();
                setTimeout(typeChar, TYPE_SPEED);
            } else {
                row.cursorEl.remove();
                setTimeout(function () { showOutput(entry, 0); }, 260);
            }
        })();
    }

    function showOutput(entry, idx) {
        if (idx < entry.lines.length) {
            addOutputLine(entry.lines[idx], idx);
            setTimeout(function () { showOutput(entry, idx + 1); }, LINE_DELAY);
        } else {
            setTimeout(function () {
                ci++;
                if (ci % commands.length === 0) { term.innerHTML = ''; }
                typeCommand();
            }, HOLD);
        }
    }

    function start() {
        if (started) return;
        started = true;
        typeCommand();
    }

    if ('IntersectionObserver' in window) {
        var termIO = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { start(); termIO.unobserve(e.target); }
            });
        }, { threshold: 0.25 });
        termIO.observe(term);
    } else {
        start();
    }
})();
</script>


{{-- ══════════════════════════════════════════════
     PAYMENT MODAL (welcome page) — QRIS & Transfer BCA
═══════════════════════════════════════════════ --}}
@if(isset($paymentSetting) && ($paymentSetting->qrisVisible() || $paymentSetting->bcaVisible()))
<div id="welcomeQrisModal"
     style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(14,16,20,0.7);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:28px;width:100%;max-width:390px;overflow:hidden;box-shadow:0 32px 80px -12px rgba(14,16,20,0.45);animation:qrisPopIn 0.3s cubic-bezier(0.34,1.56,0.64,1);">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#1A20A0,#3B46F2);padding:22px 24px 18px;position:relative;">
            <button onclick="closeQrisWelcome()" style="position:absolute;top:14px;right:14px;width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.15);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;">
                <i class="bi bi-x-lg"></i>
            </button>
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:42px;height:42px;background:rgba(255,255,255,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-credit-card" style="color:#fff;font-size:20px;"></i>
                </div>
                <div>
                    <div style="font-family:'Geist',sans-serif;font-weight:700;font-size:16px;color:#fff;letter-spacing:-0.01em;">Pilih Pembayaran</div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:2px;">QRIS atau Transfer Bank BCA</div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div style="padding:22px 24px 24px;">

            {{-- Paket info --}}
            <div style="background:#F7F8FC;border-radius:16px;padding:13px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:10.5px;color:#6C7082;font-family:'Geist Mono',monospace;text-transform:uppercase;letter-spacing:0.06em;">Paket</div>
                    <div id="wqPaketName" style="font-family:'Geist',sans-serif;font-weight:700;font-size:14px;color:#0E1014;margin-top:3px;"></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:10.5px;color:#6C7082;font-family:'Geist Mono',monospace;text-transform:uppercase;letter-spacing:0.06em;">Harga</div>
                    <div id="wqPaketPrice" style="font-family:'Geist Mono',monospace;font-weight:700;font-size:15px;color:#3B46F2;margin-top:3px;"></div>
                </div>
            </div>

            {{-- Tab switcher --}}
            <div style="display:flex;gap:8px;margin-bottom:16px;">
                @if($paymentSetting->qrisVisible())
                <button type="button" onclick="switchWelcomeTab('qris')" id="wqTabQris"
                        style="flex:1;padding:9px 0;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid #3B46F2;background:#3B46F2;color:#fff;">
                    <i class="bi bi-qr-code"></i> QRIS
                </button>
                @endif
                @if($paymentSetting->bcaVisible())
                <button type="button" onclick="switchWelcomeTab('bca')" id="wqTabBca"
                        style="flex:1;padding:9px 0;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid #E6E8F0;background:#fff;color:#6C7082;">
                    <i class="bi bi-bank"></i> Transfer BCA
                </button>
                @endif
            </div>

            {{-- TAB: QRIS --}}
            @if($paymentSetting->qrisVisible())
            <div id="wqPanelQris">
            {{-- QR Image --}}
            <div style="background:#FAFAFA;border-radius:20px;padding:18px;text-align:center;margin-bottom:18px;border:1.5px dashed #E6E8F0;">
                <div style="font-size:10.5px;color:#6C7082;font-family:'Geist Mono',monospace;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:12px;">Scan QR Code</div>
                <img src="{{ asset('storage/'.$paymentSetting->qris_image) }}"
                     alt="QRIS Routerverse"
                     style="width:210px;height:210px;object-fit:contain;margin:0 auto;display:block;border-radius:12px;">
                <div style="margin-top:12px;font-size:12px;color:#6C7082;">Semua e-wallet &amp; mobile banking</div>
            </div>

            {{-- Logo e-wallet --}}
            <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:6px;margin-bottom:18px;">
                @foreach(['GoPay','OVO','DANA','ShopeePay','BCA','Mandiri','BRI'] as $bank)
                <span style="font-size:10px;font-weight:600;color:#6C7082;background:#F7F8FC;border:1px solid #E6E8F0;border-radius:6px;padding:3px 8px;font-family:'Geist Mono',monospace;">{{ $bank }}</span>
                @endforeach
            </div>

            <a id="wqWaLink" href="#" target="_blank"
               style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:#25D366;color:#fff;font-family:'Inter',sans-serif;font-weight:600;font-size:14px;padding:13px;border-radius:14px;text-decoration:none;transition:opacity 0.2s;"
               onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
                <i class="bi bi-whatsapp" style="font-size:16px;"></i>
                Konfirmasi Pembayaran via WhatsApp
            </a>
            <p style="text-align:center;font-size:11.5px;color:#9CA3AF;margin-top:10px;line-height:1.5;">
                Setelah bayar, tap tombol di atas &amp; kirim bukti transfer untuk aktivasi layanan.
            </p>
            </div>
            @endif

            {{-- TAB: Transfer BCA --}}
            @if($paymentSetting->bcaVisible())
            <div id="wqPanelBca" style="{{ $paymentSetting->qrisVisible() ? 'display:none;' : '' }}">
                <div style="background:#FAFAFA;border-radius:20px;padding:18px;margin-bottom:18px;border:1.5px dashed #E6E8F0;">
                    <div style="font-size:10.5px;color:#6C7082;font-family:'Geist Mono',monospace;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:12px;">Transfer ke Rekening</div>
                    <div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.04em;">Bank</div>
                    <div style="font-weight:700;font-size:14px;color:#0E1014;margin-bottom:12px;">BCA</div>
                    <div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.04em;">Nomor Rekening</div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px;">
                        <span id="wqBcaNumber" style="font-weight:700;font-size:18px;color:#0E1014;">{{ $paymentSetting->bca_account_number }}</span>
                        <button type="button" onclick="copyWqBcaNumber(this)"
                                style="font-size:11px;font-weight:600;color:#6C7082;background:#fff;border:1px solid #E6E8F0;border-radius:8px;padding:6px 10px;cursor:pointer;">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.04em;">Atas Nama</div>
                    <div style="font-weight:700;font-size:14px;color:#0E1014;">{{ $paymentSetting->bca_account_name }}</div>
                </div>

                <a id="wqBcaWaLink" href="#" target="_blank"
                   style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:#25D366;color:#fff;font-family:'Inter',sans-serif;font-weight:600;font-size:14px;padding:13px;border-radius:14px;text-decoration:none;transition:opacity 0.2s;"
                   onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
                    <i class="bi bi-whatsapp" style="font-size:16px;"></i>
                    Konfirmasi Pembayaran via WhatsApp
                </a>
                <p style="text-align:center;font-size:11.5px;color:#9CA3AF;margin-top:10px;line-height:1.5;">
                    Setelah transfer, tap tombol di atas &amp; kirim bukti transfer untuk aktivasi layanan.
                </p>
            </div>
            @endif

            {{-- Instruksi (berlaku untuk kedua metode) --}}
            @if($paymentSetting->notes)
            <p style="font-size:12.5px;color:#6C7082;line-height:1.6;margin-top:16px;padding:12px;background:#F7F8FC;border-radius:12px;">
                <i class="bi bi-info-circle" style="color:#3B46F2;"></i>
                {{ $paymentSetting->notes }}
            </p>
            @endif
        </div>
    </div>
</div>

<style>
@keyframes qrisPopIn {
    0%   { transform: scale(0.88) translateY(16px); opacity: 0; }
    100% { transform: scale(1) translateY(0); opacity: 1; }
}
#welcomeQrisModal.show { display: flex !important; }
</style>

<script>
function switchWelcomeTab(tab) {
    var isQris = tab === 'qris';
    var qrisPanel = document.getElementById('wqPanelQris');
    var bcaPanel  = document.getElementById('wqPanelBca');
    if (qrisPanel) qrisPanel.style.display = isQris ? '' : 'none';
    if (bcaPanel)  bcaPanel.style.display  = isQris ? 'none' : '';

    var activeStyle   = 'flex:1;padding:9px 0;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid #3B46F2;background:#3B46F2;color:#fff;';
    var inactiveStyle = 'flex:1;padding:9px 0;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid #E6E8F0;background:#fff;color:#6C7082;';
    var tabQris = document.getElementById('wqTabQris');
    var tabBca  = document.getElementById('wqTabBca');
    if (tabQris) tabQris.setAttribute('style', isQris ? activeStyle : inactiveStyle);
    if (tabBca)  tabBca.setAttribute('style', !isQris ? activeStyle : inactiveStyle);
}

function copyWqBcaNumber(btn) {
    var number = document.getElementById('wqBcaNumber').textContent.trim();
    navigator.clipboard.writeText(number).then(function () {
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin';
        setTimeout(function () { btn.innerHTML = original; }, 1500);
    });
}

function openQrisWelcome(nama, harga) {
    document.getElementById('wqPaketName').textContent  = nama;
    document.getElementById('wqPaketPrice').textContent = harga;

    var qrisMsg = encodeURIComponent('Halo Routerverse! Saya sudah bayar paket *' + nama + '* (' + harga + ') via QRIS. Berikut bukti transfernya:');
    var wqWaLink = document.getElementById('wqWaLink');
    if (wqWaLink) wqWaLink.href = 'https://wa.me/6285173484715?text=' + qrisMsg;

    var bcaMsg = encodeURIComponent('Halo Routerverse! Saya sudah transfer BCA untuk paket *' + nama + '* (' + harga + '). Berikut bukti transfernya:');
    var wqBcaWaLink = document.getElementById('wqBcaWaLink');
    if (wqBcaWaLink) wqBcaWaLink.href = 'https://wa.me/6285173484715?text=' + bcaMsg;

    switchWelcomeTab('{{ $paymentSetting->qrisVisible() ? "qris" : "bca" }}');

    document.getElementById('welcomeQrisModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeQrisWelcome() {
    document.getElementById('welcomeQrisModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('welcomeQrisModal').addEventListener('click', function(e){
    if (e.target === this) closeQrisWelcome();
});
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeQrisWelcome();
});
</script>
@else
{{-- Belum ada metode pembayaran aktif: fallback ke WhatsApp --}}
<script>
function openQrisWelcome(nama, harga) {
    var msg = encodeURIComponent('Halo Routerverse! Saya mau pesan paket *' + nama + '* (' + harga + ')');
    window.open('https://wa.me/6285173484715?text=' + msg, '_blank');
}
</script>
@endif

</body>
</html>
@php $u = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | Routerverse</title>
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#3B46F2">
    <link rel="icon" type="image/png" href="{{ asset('image/favicon.png') }}">
    <link rel="preload" href="{{ asset('font/Poppins-Bold.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('rv.css') }}">
    <link rel="stylesheet" href="{{ asset('app.css') }}">
    @stack('head')
</head>
<body class="app-body">
<a class="skip" href="#isi">Lewati ke konten</a>

<div class="app">
    <aside class="side" id="site-nav" aria-label="Menu dashboard">
        <a class="brand side-brand" href="{{ route('welcome') }}">
            <img src="{{ asset('image/logo_sm.png') }}" width="40" height="25" alt="">
            Routerverse
        </a>
        <nav aria-label="Navigasi dashboard">
            <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
            <a href="{{ route('services.index') }}" @if(request()->routeIs('services.index')) aria-current="page" @endif>Layanan dan Paket</a>
            <a href="{{ route('vpn.index') }}" @if(request()->routeIs('vpn.*')) aria-current="page" @endif>VPN Remote</a>
            <a href="{{ route('services.my-orders') }}" @if(request()->routeIs('services.my-orders')) aria-current="page" @endif>Pesanan Saya</a>
            <a href="{{ route('points.index') }}" @if(request()->routeIs('points.*')) aria-current="page" @endif>Poin Saya <span class="pts">Rp {{ number_format((int) $u->points, 0, ',', '.') }}</span></a>
            <a href="{{ route('services.my-servers') }}" @if(request()->routeIs('services.my-servers')) aria-current="page" @endif>Server Saya</a>
            <a href="{{ route('products.index') }}" @if(request()->routeIs('products.*')) aria-current="page" @endif>Template Voucher</a>

            @if ($u->isAdmin())
                <p class="nav-title">Admin</p>
                <a href="{{ route('services.admin') }}" @if(request()->routeIs('services.admin')) aria-current="page" @endif>Kelola Pesanan</a>
                <a href="{{ route('settings.payment.edit') }}" @if(request()->routeIs('settings.payment.edit')) aria-current="page" @endif>Setting Pembayaran</a>
            @endif
        </nav>
        <div class="side-foot">
            <p><strong>{{ $u->name }}</strong>{{ $u->isAdmin() ? 'Admin' : $u->email }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-line btn-block">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="app-col" style="min-width:0">
        <header class="app-top">
            <a class="brand" href="{{ route('welcome') }}">
                <img src="{{ asset('image/logo_sm.png') }}" width="40" height="25" alt="">
                Routerverse
            </a>
            <div class="top-user"><strong>{{ $u->name }}</strong><span>{{ $u->isAdmin() ? 'Admin' : $u->email }}</span></div>
            <button id="menu-btn" class="menu-btn" type="button" aria-expanded="false" aria-controls="site-nav">Menu</button>
        </header>

        <main id="isi" class="app-content">
            @if (session('success')) <p class="notice notice-ok" role="status" style="margin:0 0 20px">{{ session('success') }}</p> @endif
            @if (session('error'))   <p class="notice notice-bad" role="alert" style="margin:0 0 20px">{{ session('error') }}</p> @endif
            @yield('content')
        </main>
    </div>
</div>

@stack('dialogs')
<script src="{{ asset('rv.js') }}" defer></script>
<script src="{{ asset('app.js') }}" defer></script>
@stack('scripts')
</body>
</html>

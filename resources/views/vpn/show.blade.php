@extends('layouts.dashboard')

@section('title', 'Kelola '.$vpn->username)

@php $wa = config('services.whatsapp.number'); $sisa = $vpn->days_left; @endphp

@section('content')
<nav class="crumbs" aria-label="Jejak halaman"><a href="{{ route('vpn.index') }}">VPN Remote</a> <span aria-hidden="true">/</span> <span aria-current="page">{{ $vpn->username }}</span></nav>

<div class="pg-head">
    <div>
        <h1>{{ $vpn->username }}</h1>
        <p>{{ $vpn->server_name }}, paket {{ $vpn->plan_name }}</p>
    </div>
    <span class="{{ $vpn->status_badge_class }}">{{ $vpn->status_label }}</span>
</div>

@if ($errors->any())
    <div class="notice notice-bad" role="alert" style="margin:0 0 20px">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@if ($vpn->status === 'pending')
    <p class="callout" style="margin-bottom:20px"><strong>Akun belum aktif.</strong> Selesaikan pembayaran di <a href="{{ route('services.my-orders') }}">Pesanan Saya</a>. Alamat remote dan script muncul setelah admin mengonfirmasi.</p>
@elseif ($vpn->display_status === 'expired')
    <p class="callout" style="margin-bottom:20px"><strong>Masa aktif habis.</strong> Akun dinonaktifkan. Perpanjang untuk mengaktifkannya lagi, port dan password Anda tetap sama.</p>
@elseif ($vpn->needs_sync)
    <p class="notice" role="status" style="margin:0 0 20px;border-color:var(--line);background:var(--paper);color:var(--ink)">Perubahan Anda tersimpan dan sedang diterapkan ke server. Biasanya selesai dalam beberapa menit.</p>
@endif
@if ($vpn->sync_error)
    <div class="notice notice-bad" role="alert" style="margin:0 0 20px">Perubahan belum bisa diterapkan ke server. Sistem mencoba lagi otomatis. Kalau tetap begini, <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Halo, akun VPN '.$vpn->username.' belum tersinkron.') }}">hubungi admin</a>.</div>
@endif

<div class="dash">
    <section class="block" aria-labelledby="ports">
        <h2 id="ports">Daftar port</h2>
        @if ($vpn->status === 'pending')
            <p style="margin-top:10px;color:var(--text-2)">Port publik dibagikan setelah akun aktif. Port tujuan yang Anda pilih:</p>
        @endif
        <ul class="portlist" aria-label="Port remote akun {{ $vpn->username }}">
            @foreach ($vpn->ports as $pt)
                <li>
                    <div>
                        <div class="sub">{{ $pt->label }}</div>
                        @if ($pt->address)
                            <div class="addr-row"><code class="addr" id="addr-{{ $pt->id }}">{{ $pt->address }}</code>
                            <button type="button" class="btn btn-line btn-sm" data-copy-text="addr-{{ $pt->id }}">Salin</button></div>
                        @else
                            <span class="sub">Muncul setelah akun aktif</span>
                        @endif
                    </div>
                    <form class="row-form" method="POST" action="{{ route('vpn.port', [$vpn, $pt]) }}">
                        @csrf @method('PATCH')
                        <label for="tp-{{ $pt->id }}" class="sub" style="width:100%">Port tujuan di router</label>
                        <input class="input" style="flex:0 0 6.5rem" type="number" id="tp-{{ $pt->id }}" name="target_port" min="1" max="65535" inputmode="numeric" value="{{ $pt->target_port }}" required>
                        <button class="btn btn-line btn-sm" type="submit" data-busy="Menyimpan...">Simpan<span class="sr-only"> {{ $pt->label }}</span></button>
                    </form>
                </li>
            @endforeach
        </ul>
        <p class="field-hint" style="margin-top:10px">Port tujuan adalah port di router Anda yang dibuka lewat alamat remote. Ubah kalau port service di router sudah Anda ganti.</p>
    </section>

    <div style="display:grid;gap:20px">
        <section class="block" aria-labelledby="det">
            <h2 id="det">Informasi akun</h2>
            <dl class="kv" style="margin-top:12px">
                <div><dt>Server</dt><dd>{{ $vpn->server_name }}</dd></div>
                <div><dt>Alamat server</dt><dd>{{ $vpn->host }}</dd></div>
                <div><dt>IP klien di VPN</dt><dd>{{ $vpn->vpn_ip ?: 'Dibagikan saat akun aktif' }}</dd></div>
                <div><dt>Username</dt><dd>{{ $vpn->username }}</dd></div>
                <div>
                    <dt>Berlaku sampai</dt>
                    <dd class="{{ $vpn->display_status === 'active' && $sisa !== null && $sisa <= 5 ? 'warn' : '' }}">
                        @if ($vpn->expired_at){{ $vpn->expired_at->format('d M Y') }}@if ($vpn->display_status === 'active' && $sisa !== null), {{ $sisa }} hari lagi @endif @else - @endif
                    </dd>
                </div>
                <div><dt>Harga perpanjangan</dt><dd>Rp {{ number_format($vpn->price, 0, ',', '.') }} / bulan</dd></div>
            </dl>
            <div class="btn-bar" style="margin-top:20px">
                @if ($renewPending)
                    <a class="btn btn-accent" href="{{ route('services.my-orders') }}">Bayar Perpanjangan</a>
                @elseif ($vpn->status !== 'pending')
                    <form method="POST" action="{{ route('vpn.renew', $vpn) }}">@csrf
                        <button class="btn btn-accent" type="submit" data-busy="Membuat pesanan...">Perpanjang 1 Bulan</button>
                    </form>
                @endif
                @if ($mikhmon)
                    <a class="btn btn-line" href="https://{{ $mikhmon->mikhmon_subdomain }}" target="_blank" rel="noopener">Buka Mikhmon<span class="sr-only"> (tab baru)</span></a>
                @else
                    <a class="btn btn-line" href="{{ route('services.index') }}">Pesan Mikhmon Online</a>
                @endif
            </div>
            @if ($mikhmon && $mikhmon->web_password)
                <dl class="kv" style="margin-top:20px">
                    <div><dt>Alamat Mikhmon</dt><dd>https://{{ $mikhmon->mikhmon_subdomain }}</dd></div>
                    <div><dt>Login pertama (dari kami)</dt><dd>{{ $mikhmon->web_user }} / <span style="user-select:all">{{ $mikhmon->web_password }}</span></dd></div>
                </dl>
                <p style="margin-top:12px;font-size:0.9375rem">Setelah masuk, Mikhmon meminta login sendiri. Login bawaannya <strong>mikhmon / 1234</strong>. Segera ganti di menu Admin, lalu tambahkan router Anda dengan IP VPN di atas.</p>
            @endif
        </section>

        <form class="block" method="POST" action="{{ route('vpn.password', $vpn) }}">
            @csrf @method('PATCH')
            <h2>Ganti password</h2>
            <div class="field" style="margin-top:12px">
                <label for="new-password">Password baru</label>
                <div class="pw">
                    <input class="input" type="password" id="new-password" name="password" required minlength="6" maxlength="24" pattern="[A-Za-z0-9]{6,24}" autocomplete="new-password" aria-describedby="pw-hint">
                    <button type="button" class="btn btn-line btn-sm" data-toggle-pw="new-password" aria-pressed="false">Tampilkan</button>
                </div>
                <p class="field-hint" id="pw-hint">Huruf dan angka, 6 sampai 24 karakter. Setelah diganti, tempel ulang script di router Anda.</p>
            </div>
            <button class="btn btn-primary" type="submit" data-busy="Menyimpan...">Simpan Password</button>
        </form>
    </div>
</div>

@if ($script)
<section class="block" aria-labelledby="scr" style="margin-top:20px">
    <h2 id="scr">Script L2TP untuk router Anda</h2>
    <p style="margin-top:8px;color:var(--text-2)">Buka Terminal di MikroTik Anda (Winbox, menu New Terminal), tempel script ini, lalu tekan Enter. Router akan tersambung ke server VPN dan bisa diakses lewat alamat remote di atas. Script sudah memuat watchdog yang menyambung ulang tunnel otomatis.</p>
    <pre class="script" id="vpn-script" tabindex="0">{{ $script }}</pre>
    <div class="btn-bar"><button type="button" class="btn btn-line" data-copy-text="vpn-script">Salin</button></div>
</section>
@endif

<section class="block" aria-labelledby="del" style="margin-top:20px">
    <h2 id="del">Hapus VPN</h2>
    <p style="margin-top:8px;color:var(--text-2)">Akun, port, dan aturan NAT {{ $vpn->username }} dihapus dari server. Router Anda tidak bisa lagi diakses lewat alamat remote. Sisa masa aktif tidak dikembalikan.</p>
    <form method="POST" action="{{ route('vpn.destroy', $vpn) }}" data-confirm="Hapus VPN {{ $vpn->username }}? Akun dan semua port-nya akan dihapus dari server dan tidak bisa dikembalikan.">
        @csrf @method('DELETE')
        <button class="btn btn-danger" type="submit" data-busy="Menghapus...">Hapus VPN</button>
    </form>
</section>
@endsection

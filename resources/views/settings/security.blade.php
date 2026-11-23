@extends('layouts.dashboard')

@section('title', 'Keamanan Akun')

@section('content')
<div class="pg-head">
    <div>
        <h1>Keamanan akun</h1>
        <p>Atur cara Anda membuktikan bahwa akun ini milik Anda saat masuk.</p>
    </div>
</div>

<div class="dash">
    <section class="block" aria-labelledby="sec-email">
        <h2 id="sec-email">Email</h2>
        <dl class="kv" style="margin-top:12px">
            <div><dt>Alamat email</dt><dd>{{ $user->email }}</dd></div>
            <div><dt>Status</dt><dd>{{ $user->hasVerifiedEmail() ? 'Terverifikasi' : 'Belum diverifikasi' }}</dd></div>
        </dl>
        <p class="field-hint" style="margin-top:10px">Kode verifikasi dua langkah dikirim ke alamat ini. Jika email Anda berubah, hubungi admin.</p>
    </section>

    <section class="block" aria-labelledby="sec-otp">
        <h2 id="sec-otp">Verifikasi dua langkah</h2>

        @if ($errors->any())
            <div class="notice notice-bad" role="alert" style="margin-top:12px"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @if (! $otpAvailable)
            <p style="margin-top:10px;color:var(--text-2)">Fitur ini sedang dinonaktifkan oleh admin. Login hanya memakai email dan password.</p>
        @else
            <dl class="kv" style="margin-top:12px">
                <div><dt>Status</dt><dd>{{ $otpOn ? 'Aktif' : 'Tidak aktif' }}</dd></div>
            </dl>
            <p class="field-hint" style="margin-top:10px">Setelah email dan password benar, kami mengirim kode 6 angka ke email Anda. Kode itu dimasukkan untuk menyelesaikan login.</p>

            @if ($otpForced)
                <p class="field-hint" style="margin-top:10px">Akun admin wajib memakai verifikasi dua langkah, jadi tidak bisa dimatikan.</p>
            @elseif ($otpOn)
                <form method="POST" action="{{ route('security.otp.disable') }}" style="margin-top:16px">
                    @csrf
                    <div class="field">
                        <label for="pw-off">Password Anda</label>
                        <input class="input" type="password" id="pw-off" name="password" autocomplete="current-password" required aria-describedby="pw-off-hint">
                        <p class="field-hint" id="pw-off-hint">Diminta supaya orang lain tidak bisa mematikannya dari sesi Anda yang terbuka.</p>
                    </div>
                    <button type="submit" class="btn btn-line" data-busy="Memproses...">Matikan verifikasi dua langkah</button>
                </form>
            @else
                <form method="POST" action="{{ route('security.otp.enable') }}" style="margin-top:16px">
                    @csrf
                    <div class="field">
                        <label for="pw-on">Password Anda</label>
                        <input class="input" type="password" id="pw-on" name="password" autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary" data-busy="Memproses...">Aktifkan verifikasi dua langkah</button>
                </form>
            @endif
        @endif
    </section>
</div>
@endsection

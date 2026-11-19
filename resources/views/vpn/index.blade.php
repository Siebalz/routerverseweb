@extends('layouts.dashboard')

@section('title', 'VPN Remote')

@section('content')
<div class="pg-head">
    <div>
        <h1>VPN Remote</h1>
        <p>Akses Winbox, API, atau port lain di router Anda dari mana saja lewat alamat remote.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('vpn.create') }}">Order VPN Remote</a>
</div>

@if ($accounts->isEmpty())
    <div class="empty">
        <h2 style="font-size:1.25rem">Belum ada akun VPN Remote</h2>
        <p>Pilih server, tentukan username dan port tujuan, lalu bayar. Akun aktif setelah admin mengonfirmasi.</p>
        <a class="btn btn-primary" href="{{ route('vpn.create') }}">Order VPN Remote</a>
    </div>
@else
    <div class="table-wrap">
        <table class="data" style="min-width:680px">
            <caption class="sr-only">Daftar akun VPN Remote Anda</caption>
            <thead>
                <tr>
                    <th scope="col">Aksi</th>
                    <th scope="col">Username</th>
                    <th scope="col">Alamat remote</th>
                    <th scope="col">Berlaku sampai</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accounts as $a)
                    @php $sisa = $a->days_left; @endphp
                    <tr>
                        <td><a class="btn btn-line btn-sm" href="{{ route('vpn.show', $a) }}">Kelola<span class="sr-only"> {{ $a->username }}</span></a></td>
                        <td><strong>{{ $a->username }}</strong><div class="sub">{{ $a->server_name }}, {{ $a->plan_name }}</div></td>
                        <td>
                            @php $live = $a->ports->whereNotNull('public_port'); @endphp
                            @if ($live->isEmpty())
                                <span class="sub">Muncul setelah akun aktif</span>
                            @else
                                @foreach ($live as $pt)
                                    <div class="portline"><code class="addr">{{ $pt->address }}</code> <span aria-label="diteruskan ke port">→</span> <code class="addr">{{ $pt->target_port }}</code> <span class="sub">{{ $pt->label }}</span></div>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if ($a->expired_at)
                                {{ $a->expired_at->format('d M Y') }}
                                @if ($a->display_status === 'active' && $sisa !== null)<div class="sub {{ $sisa <= 5 ? 'warn' : '' }}">{{ $sisa }} hari lagi</div>@endif
                            @else
                                <span class="sub">-</span>
                            @endif
                        </td>
                        <td><span class="{{ $a->status_badge_class }}">{{ $a->status_label }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

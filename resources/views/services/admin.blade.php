@extends('layouts.dashboard')

@section('title', 'Kelola Pesanan')

@section('content')
<div class="pg-head">
    <div>
        <h1>Kelola pesanan</h1>
        <p>Semua pesanan layanan dan paket yang masuk dari pelanggan.</p>
    </div>
</div>

@if ($orders->isEmpty())
    <div class="empty">
        <h2 style="font-size:1.25rem">Belum ada pesanan masuk</h2>
        <p>Pesanan baru dari pelanggan akan tampil di sini.</p>
    </div>
@else
    <div class="table-wrap">
        <table class="data">
            <caption class="sr-only">Daftar pesanan pelanggan</caption>
            <thead>
                <tr>
                    <th scope="col">Pemesan</th>
                    <th scope="col">Layanan</th>
                    <th scope="col">Catatan</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Expired</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ubah status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td><strong>{{ $order->user->name ?? 'Pengguna dihapus' }}</strong><div class="sub">{{ $order->user->email ?? '' }}</div></td>
                        <td><strong>{{ $order->service_name }}</strong><div class="sub">{{ $order->price_label }}</div></td>
                        <td class="sub">{{ $order->notes ?: '-' }}</td>
                        <td class="sub">{{ $order->created_at->format('d M Y, H:i') }}</td>
                        <td class="sub">
                            @if ($order->expired_at)
                                {{ $order->expired_at->format('d M Y') }}
                                @if ($order->status === 'expired')<div class="warn" style="color:var(--bad)">Sudah expired</div>@endif
                            @else - @endif
                        </td>
                        <td><span class="{{ $order->status_badge_class }}">{{ $order->status_label }}</span></td>
                        <td>
                            <form action="{{ route('services.update-status', $order) }}" method="POST" class="row-form">
                                @csrf @method('PATCH')
                                <label class="sr-only" for="status-{{ $order->id }}">Status pesanan {{ $order->service_name }}</label>
                                <select class="input" id="status-{{ $order->id }}" name="status">
                                    @foreach (\App\Models\Order::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                            </form>
                            @if ($order->expired_at)
                                <form action="{{ route('services.renew', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-line btn-sm">Perpanjang 1 Bulan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top:8px">{{ $orders->links('pagination.rv') }}</div>
@endif
@endsection

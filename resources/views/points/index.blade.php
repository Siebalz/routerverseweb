@extends('layouts.dashboard')

@section('title', 'Poin Saya')

@section('content')
<div class="pg-head">
    <div>
        <h1>Poin saya</h1>
        <p>Poin dipakai untuk membayar layanan. Pesanan yang dibayar dengan poin langsung diproses.</p>
    </div>
</div>

<div class="dash">
    <section class="block" aria-labelledby="saldo">
        <h2 id="saldo">Saldo</h2>
        <p class="bal"><span>Rp</span> {{ number_format($balance, 0, ',', '.') }}</p>
        <p class="field-hint">1 poin sama dengan Rp 1.</p>
        <ul class="rules">
            <li>Poin tidak kedaluwarsa.</li>
            <li>Poin tidak bisa ditarik atau diuangkan kembali. Poin hanya untuk membayar layanan di web ini.</li>
            <li>Top up minimal Rp {{ number_format($minTopup, 0, ',', '.') }}. Biaya pembayaran gateway ditanggung pembeli dan ditampilkan sebelum Anda membayar.</li>
        </ul>
    </section>

    <section class="block" aria-labelledby="topup">
        <h2 id="topup">Top up</h2>
        @if ($returned)
            <p class="notice notice-ok" role="status" style="margin-top:12px">Terima kasih. Poin masuk otomatis begitu pembayaran lunas, biasanya dalam hitungan menit. Muat ulang halaman ini untuk melihat saldo.</p>
        @endif
        @if ($errors->any())
            <div class="notice notice-bad" role="alert" style="margin-top:12px"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if ($gatewayReady)
            <form method="POST" action="{{ route('points.topup') }}" id="topup-form" data-fee-flat="{{ $feeFlat }}" data-fee-percent="{{ $feePercent }}" style="margin-top:12px">
                @csrf
                <div class="field">
                    <label for="amount">Nominal top up (Rp)</label>
                    <input class="input" type="number" id="amount" name="amount" min="{{ $minTopup }}" max="{{ $maxTopup }}" step="1" inputmode="numeric" value="{{ old('amount', $minTopup) }}" required aria-describedby="amount-hint" @error('amount') aria-invalid="true" @enderror>
                    <p class="field-hint" id="amount-hint">Minimal Rp {{ number_format($minTopup, 0, ',', '.') }}. Pilih cepat:</p>
                </div>
                <div class="chips" role="group" aria-label="Pilihan nominal">
                    @foreach ([10000, 25000, 50000, 100000] as $q)
                        @if ($q >= $minTopup)<button type="button" class="btn btn-line btn-sm" data-amount="{{ $q }}">{{ number_format($q, 0, ',', '.') }}</button>@endif
                    @endforeach
                </div>
                <dl class="kv" style="margin-top:14px">
                    <div><dt>Poin yang masuk</dt><dd id="tp-points">Rp 0</dd></div>
                    <div><dt>Biaya pembayaran (ditanggung pembeli)</dt><dd id="tp-fee">Rp 0</dd></div>
                    <div><dt>Total bayar</dt><dd id="tp-total"><strong>Rp 0</strong></dd></div>
                </dl>
                <div class="btn-bar" style="margin-top:16px">
                    <button type="submit" class="btn btn-primary" data-busy="Membuka halaman bayar...">Top Up Sekarang</button>
                </div>
                <p class="field-hint" style="margin-top:10px">Anda akan diarahkan ke halaman pembayaran Duitku (QRIS, virtual account, e-wallet, dan lainnya).</p>
            </form>
        @else
            <p style="margin-top:10px;color:var(--text-2)">Top up otomatis belum aktif. Hubungi admin untuk menambah poin.</p>
            <div class="btn-bar" style="margin-top:16px">
                <a class="btn btn-line" href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ rawurlencode('Halo Routerverse, saya mau top up poin.') }}" target="_blank" rel="noopener">Chat Admin di WhatsApp<span class="sr-only"> (tab baru)</span></a>
            </div>
        @endif
        @if ($topups->isNotEmpty())
            <h3 style="margin:20px 0 6px;font-size:1rem">Top up terakhir</h3>
            <ul class="list ledger">
                @foreach ($topups as $t)
                    <li>
                        <div><span class="name">Rp {{ number_format($t->amount, 0, ',', '.') }}</span><p class="meta">{{ $t->created_at->format('d M Y, H:i') }}, total bayar Rp {{ number_format($t->total, 0, ',', '.') }}</p></div>
                        <div class="amt">
                            <span class="{{ $t->status === 'paid' ? 'order-badge-done' : ($t->status === 'failed' ? 'order-badge-cancel' : 'order-badge-pending') }}">{{ $t->status_label }}</span>
                            @if ($t->status === 'pending' && $t->payment_url)<p class="meta"><a href="{{ $t->payment_url }}">Lanjutkan pembayaran</a></p>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>

<section class="block" aria-labelledby="riwayat" style="margin-top:20px">
    <h2 id="riwayat">Riwayat poin</h2>
    @if ($ledgers->isEmpty())
        <p style="margin-top:10px;color:var(--text-2)">Belum ada riwayat. Setelah top up atau membayar pesanan, catatannya muncul di sini.</p>
    @else
        <ul class="list ledger">
            @foreach ($ledgers as $l)
                <li>
                    <div>
                        <span class="name">{{ $l->type_label }}</span>
                        @if ($l->note)<p class="meta">{{ $l->note }}</p>@endif
                        <p class="meta">{{ $l->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="amt">
                        <strong class="{{ $l->amount > 0 ? 'plus' : 'minus' }}">{{ $l->amount > 0 ? '+' : '-' }}Rp {{ number_format(abs($l->amount), 0, ',', '.') }}</strong>
                        <p class="meta">Saldo Rp {{ number_format($l->balance_after, 0, ',', '.') }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
        {{ $ledgers->links('pagination.rv') }}
    @endif
</section>
@endsection

{{--
    Dialog pembayaran bersama (landing dan detail produk).
    Metode ditampilkan sebagai tab. Saat payment gateway aktif, tambahkan satu tab dan satu panel
    di sini; status pesanan memakai kelas .status-wait / .status-paid / .status-expired / .status-failed.
--}}
@php
    $qris = $paymentSetting->qrisVisible();
    $bca  = $paymentSetting->bcaVisible();
    $methods = collect([$qris ? 'qris' : null, $bca ? 'bca' : null])->filter()->implode(',');
@endphp
<dialog id="pay-dialog" class="dialog" aria-labelledby="pay-title" data-wa="{{ config('services.whatsapp.number') }}" data-methods="{{ $methods }}">
    <form method="dialog" class="dialog-head">
        <h2 id="pay-title">Pembayaran</h2>
        <button type="submit">Tutup</button>
    </form>
    <div class="dialog-body">
        <dl class="pay-sum">
            <div><dt>Item</dt><dd data-pay="name"></dd></div>
            <div><dt>Harga</dt><dd data-pay="price"></dd></div>
        </dl>

        @if ($qris && $bca)
            <div class="tabs" role="tablist" aria-label="Metode pembayaran">
                <button type="button" role="tab" data-method="qris" aria-selected="true">QRIS</button>
                <button type="button" role="tab" data-method="bca" aria-selected="false">Transfer BCA</button>
            </div>
        @endif

        @if ($qris)
            <div class="qris" data-panel="qris">
                <img src="{{ asset('storage/'.$paymentSetting->qris_image) }}" width="220" height="220" alt="Kode QRIS Routerverse" loading="lazy">
                <p class="pay-note">Scan dengan e-wallet atau mobile banking. Masukkan nominal sesuai harga.</p>
            </div>
        @endif

        @if ($bca)
            <dl class="bank" data-panel="bca" @if($qris) hidden @endif>
                <div><dt>Bank</dt><dd>BCA</dd></div>
                <div>
                    <dt>Nomor rekening</dt>
                    <dd class="bank-no">
                        <span id="bca-number">{{ $paymentSetting->bca_account_number }}</span>
                        <button type="button" class="btn btn-line btn-sm" data-copy="bca-number">Salin</button>
                    </dd>
                </div>
                <div><dt>Atas nama</dt><dd>{{ $paymentSetting->bca_account_name }}</dd></div>
            </dl>
        @endif

        @unless ($qris || $bca)
            <p class="pay-note">Metode pembayaran online belum diaktifkan. Pesan dan bayar lewat WhatsApp admin.</p>
        @endunless

        @if ($paymentSetting->notes)
            <p class="pay-note">{{ $paymentSetting->notes }}</p>
        @endif

        <a class="btn btn-accent btn-block" data-pay="wa" href="https://wa.me/{{ config('services.whatsapp.number') }}" target="_blank" rel="noopener">
            {{ ($qris || $bca) ? 'Kirim Bukti Bayar via WhatsApp' : 'Pesan via WhatsApp' }}
        </a>
    </div>
</dialog>

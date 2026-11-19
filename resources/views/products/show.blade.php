@extends('layouts.site')

@section('title', $product->name.' | Routerverse')
@section('description', \Illuminate\Support\Str::limit(strip_tags($product->description ?: 'Template voucher hotspot dari Routerverse.'), 150))

@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $photos  = $product->all_image_paths;
    $harga   = 'Rp '.number_format($product->price, 0, ',', '.');
    $canPay  = $paymentSetting->qrisVisible() || $paymentSetting->bcaVisible();
    $wa      = config('services.whatsapp.number');
@endphp

@section('content')
<div class="container">
    <nav class="crumbs" aria-label="Jejak halaman">
        <ol>
            <li><a href="{{ route('welcome') }}">Beranda</a></li>
            <li><a href="{{ route('products.index') }}">Template Voucher</a></li>
            <li aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="detail">
        <div>
            <div class="gallery-main">
                @if ($photos->isNotEmpty())
                    <img id="gallery-main-img" src="{{ asset('storage/'.$photos->first()) }}" width="640" height="640" alt="{{ $product->name }}, foto 1" fetchpriority="high">
                @else
                    <span>Belum ada gambar</span>
                @endif
            </div>
            @if ($photos->count() > 1)
                <div class="thumbs" role="group" aria-label="Pilih foto">
                    @foreach ($photos as $i => $path)
                        <button type="button" data-gallery-src="{{ asset('storage/'.$path) }}" data-gallery-alt="{{ $product->name }}, foto {{ $i + 1 }}"
                                aria-label="Tampilkan foto {{ $i + 1 }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}">
                            <img src="{{ asset('storage/'.$path) }}" width="64" height="64" alt="" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="detail-info">
            <span class="tile-cat">{{ $product->category ?? 'Voucher Hotspot' }}</span>
            <h1 style="font-size:clamp(1.6rem,3.4vw,2.25rem)">{{ $product->name }}</h1>
            @if ($product->sold_count > 0)
                <p class="tile-meta">Terjual {{ $product->sold_count }}</p>
            @endif
            <p class="detail-price"><span class="price-amber">{{ $harga }}</span></p>
            <p class="detail-desc">{{ $product->description ?: 'Tidak ada deskripsi untuk produk ini.' }}</p>

            <div class="includes">
                <ul class="ticks">
                    <li>File siap pakai, tinggal cetak atau jual</li>
                    <li>Bisa request custom logo dan nama usaha</li>
                    <li>Konsultasi langsung dengan tim Routerverse</li>
                </ul>
            </div>

            <div class="btn-row">
                <a class="btn btn-accent" href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Halo, saya mau beli template: '.$product->name) }}" target="_blank" rel="noopener">Beli via WhatsApp</a>
                @if ($paymentSetting->qrisVisible())
                    <button type="button" class="btn btn-line" data-pay-open data-pay-kind="template" data-pay-tab="qris" data-pay-name="{{ $product->name }}" data-pay-price="{{ $harga }}">Bayar QRIS</button>
                @endif
                @if ($paymentSetting->bcaVisible())
                    <button type="button" class="btn btn-line" data-pay-open data-pay-kind="template" data-pay-tab="bca" data-pay-name="{{ $product->name }}" data-pay-price="{{ $harga }}">Transfer BCA</button>
                @endif
            </div>

            @if ($isAdmin)
                <div class="admin-bar">
                    <a class="btn btn-line btn-sm" href="{{ route('products.edit', $product) }}">Edit Produk</a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" data-confirm="Hapus produk ini?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section style="padding-bottom:64px" aria-labelledby="judul-lain">
            <h2 id="judul-lain" style="font-size:1.375rem;margin-bottom:20px">Template lainnya</h2>
            <ul class="tiles">
                @foreach ($related as $item)
                    <li class="tile">
                        <a class="tile-link" href="{{ route('products.show', $item) }}">
                            <div class="tile-img">
                                @if ($item->cover_image)
                                    <img src="{{ asset('storage/'.$item->cover_image) }}" width="400" height="400" alt="{{ $item->name }}" loading="lazy">
                                @else
                                    <span>Belum ada gambar</span>
                                @endif
                            </div>
                            <div class="tile-body">
                                <span class="tile-name">{{ $item->name }}</span>
                                <span class="tile-price">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection

@push('dialogs')
    @include('components.payment-dialog', ['paymentSetting' => $paymentSetting])
@endpush

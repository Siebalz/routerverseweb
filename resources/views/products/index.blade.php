@extends('layouts.site')

@section('title', 'Template Voucher Hotspot | Routerverse')
@section('description', 'Template voucher hotspot siap cetak untuk warnet, RT/RW Net, dan usaha WiFi. Logo dan harga bisa disesuaikan.')

@php $isAdmin = auth()->check() && auth()->user()->isAdmin(); @endphp

@section('content')
<div class="container">
    <header class="page-head">
        <x-arcs style="width:32px;height:32px;margin-bottom:12px" />
        <h1 style="font-size:clamp(1.75rem,4vw,2.5rem)">Template voucher hotspot</h1>
        <p>Desain voucher siap pakai untuk warnet, RT/RW Net, atau usaha WiFi Anda. Logo dan harga bisa disesuaikan, pemesanan lewat WhatsApp.</p>
    </header>

    <form class="toolbar" method="GET" action="{{ route('products.index') }}" role="search" aria-label="Cari template">
        <div class="field">
            <label for="q">Cari nama template</label>
            <input class="input" type="search" id="q" name="q" value="{{ request('q') }}">
        </div>
        <div class="field">
            <label for="category">Kategori</label>
            <select class="input" id="category" name="category">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') == $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="sort">Urutkan</label>
            <select class="input" id="sort" name="sort">
                @foreach (['terbaru' => 'Terbaru', 'terlaris' => 'Terlaris', 'termurah' => 'Termurah', 'termahal' => 'Termahal'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('sort', 'terbaru') == $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary" data-busy="Mencari...">Cari</button>
    </form>

    @if ($isAdmin)
        <p style="margin-bottom:20px"><a class="btn btn-accent" href="{{ route('products.create') }}">Tambah Produk</a></p>
    @endif

    @if ($products->isEmpty())
        <div class="empty" style="margin-bottom:64px">
            <h2 style="font-size:1.25rem">
                @if (request('q') || request('category')) Template tidak ditemukan @else Belum ada template @endif
            </h2>
            <p>
                @if (request('q') || request('category'))
                    Coba kata kunci atau kategori lain, atau hapus filter untuk melihat semua template.
                @else
                    Template baru sedang disiapkan.
                @endif
            </p>
            @if (request('q') || request('category'))
                <a class="btn btn-line" href="{{ route('products.index') }}">Hapus Filter</a>
            @elseif ($isAdmin)
                <a class="btn btn-primary" href="{{ route('products.create') }}">Tambah Produk Pertama</a>
            @endif
        </div>
    @else
        <p class="result-count" role="status">Menampilkan {{ $products->count() }} dari {{ $products->total() }} template</p>
        <ul class="tiles">
            @foreach ($products as $product)
                <li class="tile">
                    <a class="tile-link" href="{{ route('products.show', $product) }}">
                        <div class="tile-img">
                            @if ($product->cover_image)
                                <img src="{{ asset('storage/'.$product->cover_image) }}" width="400" height="400" alt="{{ $product->name }}" loading="lazy">
                            @else
                                <span>Belum ada gambar</span>
                            @endif
                        </div>
                        <div class="tile-body">
                            <span class="tile-cat">{{ $product->category ?? 'Voucher Hotspot' }}</span>
                            <span class="tile-name">{{ $product->name }}</span>
                            <span class="tile-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            @if ($product->sold_count > 0)
                                <span class="tile-meta">Terjual {{ $product->sold_count }}</span>
                            @endif
                            @if ($isAdmin && ! $product->is_active)
                                <span class="tile-flag">Nonaktif</span>
                            @endif
                        </div>
                    </a>
                    @if ($isAdmin)
                        <div class="tile-admin">
                            <a class="btn btn-line btn-sm" href="{{ route('products.edit', $product) }}" aria-label="Edit {{ $product->name }}">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" data-confirm="Hapus produk ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" aria-label="Hapus {{ $product->name }}">Hapus</button>
                            </form>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
        <div style="padding-bottom:64px">{{ $products->links('pagination.rv') }}</div>
    @endif
</div>
@endsection

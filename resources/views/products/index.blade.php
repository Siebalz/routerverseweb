@extends('layouts.shop')

@section('title', 'Template Voucher Hotspot')

@push('styles')
<style>
.product-card { transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
.product-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(79,70,229,0.10); border-color: #c7caef; }
.product-card .thumb img { transition: transform .35s ease; }
.product-card:hover .thumb img { transform: scale(1.06); }
.name-clamp { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }

/* Custom select styling */
select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239aa0b3' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="relative overflow-hidden rounded-2xl mb-6"
     style="background: linear-gradient(120deg, #2935c9 0%, #3b46f2 55%, #5b63f8 100%); padding: 40px 36px;">
    {{-- Decorative orbs --}}
    <div class="absolute w-64 h-64 rounded-full border border-white/10 -top-20 -right-14 pointer-events-none"></div>
    <div class="absolute w-36 h-36 rounded-full border border-white/10 bottom-[-40px] right-40 pointer-events-none"></div>

    <div class="relative z-10">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-white/15 text-white px-3 py-1.5 rounded-full mb-4">
            <i class="bi bi-patch-check-fill"></i> Template Resmi Routerverse
        </span>
        <h1 class="text-white font-extrabold text-2xl md:text-3xl max-w-xl mb-2 leading-tight">
            Voucher Hotspot Siap Cetak, Tinggal Pilih &amp; Pakai
        </h1>
        <p class="text-white/80 text-sm max-w-lg mb-5 leading-relaxed">
            Desain voucher hotspot rapi untuk warnet, RT/RW Net, atau bisnis WiFi-an Anda — gak perlu desain dari nol.
        </p>
        <div class="flex flex-wrap gap-4">
            <span class="text-white/90 text-xs flex items-center gap-1.5"><i class="bi bi-check2-circle"></i> File langsung bisa dipakai</span>
            <span class="text-white/90 text-xs flex items-center gap-1.5"><i class="bi bi-pencil-square"></i> Bisa custom logo &amp; harga</span>
            <span class="text-white/90 text-xs flex items-center gap-1.5"><i class="bi bi-whatsapp"></i> Pemesanan via WhatsApp</span>
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div class="bg-white border border-gray-100 rounded-2xl px-4 py-3 mb-5 flex flex-wrap gap-3 items-center shadow-sm">
    <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-2.5 items-center flex-1">

        {{-- Search --}}
        <div class="relative flex-1 min-w-[180px]">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
            <input type="text" name="q"
                   class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-xl outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                   placeholder="Cari nama template..."
                   value="{{ request('q') }}">
        </div>

        {{-- Category --}}
        <select name="category" onchange="this.form.submit()"
                class="text-sm border border-gray-200 rounded-xl px-3 py-2 pr-8 bg-white text-gray-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all cursor-pointer">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>

        {{-- Sort --}}
        <select name="sort" onchange="this.form.submit()"
                class="text-sm border border-gray-200 rounded-xl px-3 py-2 pr-8 bg-white text-gray-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all cursor-pointer">
            <option value="terbaru"  {{ request('sort', 'terbaru') == 'terbaru'  ? 'selected' : '' }}>Terbaru</option>
            <option value="terlaris" {{ request('sort') == 'terlaris' ? 'selected' : '' }}>Terlaris</option>
            <option value="termurah" {{ request('sort') == 'termurah' ? 'selected' : '' }}>Termurah</option>
            <option value="termahal" {{ request('sort') == 'termahal' ? 'selected' : '' }}>Termahal</option>
        </select>

        <button type="submit"
                class="px-4 py-2 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-dark transition-colors">
            Cari
        </button>
    </form>

    @auth
        @if (Auth::user()->isAdmin())
            <div class="hidden md:block w-px h-6 bg-gray-100"></div>
            <a href="{{ route('products.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-dark transition-colors no-underline whitespace-nowrap">
                <i class="bi bi-plus-lg"></i> Tambah Produk
            </a>
        @endif
    @endauth
</div>

{{-- Result count --}}
@if (! $products->isEmpty())
    <p class="text-xs text-gray-400 mb-4">Menampilkan {{ $products->count() }} dari {{ $products->total() }} template</p>
@endif

{{-- Empty state --}}
@if ($products->isEmpty())
    <div class="bg-white border border-dashed border-indigo-200 rounded-2xl py-16 px-6 text-center">
        <i class="bi bi-shop text-4xl text-indigo-200 block mb-3"></i>
        <p class="text-gray-400 text-sm mb-0">
            @if (request('q') || request('category'))
                Template yang Anda cari belum ada. Coba kata kunci lain ya.
            @else
                Belum ada produk template voucher.
            @endif
        </p>
        @auth
            @if (Auth::user()->isAdmin())
                <a href="{{ route('products.create') }}"
                   class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-dark transition-colors no-underline">
                    <i class="bi bi-plus-lg"></i> Tambah Produk Pertama
                </a>
            @endif
        @endauth
    </div>

{{-- Product grid --}}
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach ($products as $product)
            <div class="product-card bg-white border border-gray-100 rounded-2xl overflow-hidden flex flex-col relative">

                {{-- Admin overlay --}}
                @auth
                    @if (Auth::user()->isAdmin())
                        <div class="absolute top-2 right-2 flex gap-1.5 z-10">
                            <a href="{{ route('products.edit', $product) }}"
                               class="w-7 h-7 rounded-lg bg-white/90 text-gray-500 hover:text-brand flex items-center justify-center text-sm shadow-sm transition-colors"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                  onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-7 h-7 rounded-lg bg-white/90 text-gray-500 hover:text-red-500 flex items-center justify-center text-sm shadow-sm transition-colors"
                                        title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        @if (!$product->is_active)
                            <span class="absolute top-2 left-2 z-10 text-[10px] font-bold bg-gray-500 text-white px-2 py-0.5 rounded-full">Nonaktif</span>
                        @endif
                    @endif
                @endauth

                {{-- Thumbnail --}}
                <a href="{{ route('products.show', $product) }}" class="thumb block w-full aspect-square bg-gray-50 overflow-hidden">
                    @if ($product->cover_image)
                        <img src="{{ asset('storage/'.$product->cover_image) }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="bi bi-image text-4xl text-gray-200"></i>
                        </div>
                    @endif
                </a>

                {{-- Body --}}
                <div class="p-3 flex flex-col flex-1">
                    <span class="inline-block text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md mb-2 w-fit">
                        {{ $product->category ?? 'Voucher Hotspot' }}
                    </span>
                    <a href="{{ route('products.show', $product) }}" class="no-underline">
                        <p class="name-clamp text-sm font-semibold text-gray-900 hover:text-brand mb-2 leading-snug transition-colors">
                            {{ $product->name }}
                        </p>
                    </a>
                    <p class="font-bold text-gray-900 text-base mb-0.5">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mb-3 flex items-center gap-1">
                        <i class="bi bi-bag-check"></i> Terjual {{ $product->sold_count }}
                    </p>
                    <a href="{{ route('products.show', $product) }}"
                       class="mt-auto block text-center text-sm font-semibold py-2 rounded-xl border-2 border-brand text-brand hover:bg-brand hover:text-white transition-all no-underline">
                        Lihat Detail
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
@endif

@endsection
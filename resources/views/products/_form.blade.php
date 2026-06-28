@csrf

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <div class="mb-3">
            <label class="form-label fw-semibold">Nama Produk</label>
            <input type="text" name="name" class="form-control" placeholder="Contoh: Template Voucher Hotspot Elegan 58mm"
                value="{{ old('name', $product->name ?? '') }}" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Kategori</label>
                <input type="text" name="category" class="form-control" placeholder="Contoh: Thermal 58mm"
                    value="{{ old('category', $product->category ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Harga (Rp)</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="15000"
                    value="{{ old('price', $product->price ?? '') }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi</label>
            <textarea name="description" rows="6" class="form-control" placeholder="Jelaskan detail template voucher ini, ukuran, format file, dll.">{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="is_active">Tampilkan produk ini ke pembeli</label>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Gambar Produk (bisa lebih dari 1)</label>

        @if (!empty($product) && $product->images->isNotEmpty())
            <div class="row g-2 mb-2">
                @foreach ($product->images as $img)
                    <div class="col-6">
                        <div class="position-relative">
                            <img src="{{ asset('storage/'.$img->path) }}" class="img-fluid rounded" alt="Gambar produk">
                            <label class="form-check d-flex align-items-center gap-1 small mt-1 text-danger">
                                <input type="checkbox" class="form-check-input" name="delete_images[]" value="{{ $img->id }}">
                                Hapus gambar ini
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="form-text mb-2">Centang gambar yang ingin dihapus, lalu klik simpan.</div>
        @elseif (!empty($product) && $product->image)
            <img src="{{ asset('storage/'.$product->image) }}" class="img-fluid rounded mb-2" alt="Preview saat ini">
        @endif

        <input type="file" name="images[]" accept="image/*" class="form-control" multiple>
        <div class="form-text">Bisa pilih beberapa gambar sekaligus (Ctrl/Cmd + klik). Format JPG/PNG, maksimal 2MB per gambar. Gambar pertama jadi sampul, dan kalau lebih dari 1 akan otomatis jadi slideshow di halaman detail.</div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-brand-submit">
        {{ isset($product) ? 'Simpan Perubahan' : 'Tambah Produk' }}
    </button>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>

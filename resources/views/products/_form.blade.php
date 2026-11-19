@csrf

@if ($errors->any())
    <div class="notice notice-bad" role="alert" style="margin:0 0 20px">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="product-form">
    <div>
        <div class="field">
            <label for="name">Nama produk</label>
            <input class="input" type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" placeholder="Contoh: Template Voucher Hotspot Elegan 58mm" required>
        </div>
        <div class="cols-2">
            <div class="field">
                <label for="category">Kategori</label>
                <input class="input" type="text" id="category" name="category" value="{{ old('category', $product->category ?? '') }}" placeholder="Contoh: Thermal 58mm">
            </div>
            <div class="field">
                <label for="price">Harga (Rp)</label>
                <input class="input" type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" placeholder="15000" required>
            </div>
        </div>
        <div class="field">
            <label for="description">Deskripsi</label>
            <textarea class="input" id="description" name="description" rows="6" placeholder="Jelaskan ukuran, format file, dan isi template.">{{ old('description', $product->description ?? '') }}</textarea>
        </div>
        <div class="check">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
            <label for="is_active">Tampilkan produk ini ke pembeli</label>
        </div>
    </div>

    <div>
        <fieldset class="fieldset" style="margin:0">
            <legend>Gambar</legend>
            @if (!empty($product) && $product->images->isNotEmpty())
                <div class="img-grid">
                    @foreach ($product->images as $img)
                        <div>
                            <img src="{{ asset('storage/'.$img->path) }}" width="160" height="160" alt="Gambar produk {{ $loop->iteration }}">
                            <div class="check" style="margin:8px 0 0">
                                <input type="checkbox" id="del-{{ $img->id }}" name="delete_images[]" value="{{ $img->id }}">
                                <label for="del-{{ $img->id }}" style="font-weight:400;color:var(--bad)">Hapus gambar ini</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="field-hint" style="margin-bottom:12px">Centang gambar yang ingin dihapus, lalu simpan.</p>
            @elseif (!empty($product) && $product->image)
                <img class="preview" src="{{ asset('storage/'.$product->image) }}" width="200" height="200" alt="Gambar produk saat ini">
            @endif
            <div class="field" style="margin:0">
                <label for="images">Tambah gambar</label>
                <input class="input" type="file" id="images" name="images[]" accept="image/*" multiple aria-describedby="img-hint">
                <p class="field-hint" id="img-hint">Boleh lebih dari satu. JPG atau PNG, maksimal 2 MB per gambar. Gambar pertama jadi sampul.</p>
            </div>
        </fieldset>
    </div>
</div>

<div class="btn-bar">
    <button type="submit" class="btn btn-primary" data-busy="Menyimpan...">{{ isset($product) ? 'Simpan Perubahan' : 'Tambah Produk' }}</button>
    <a class="btn btn-line" href="{{ route('products.index') }}">Batal</a>
</div>

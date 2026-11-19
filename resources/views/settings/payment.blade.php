@extends('layouts.dashboard')

@section('title', 'Setting Pembayaran')

@section('content')
<div class="pg-head">
    <div>
        <h1>Setting pembayaran</h1>
        <p>Atur QRIS dan transfer BCA. Setiap metode bisa diaktifkan atau dimatikan sendiri, dan perubahan langsung berlaku di halaman produk dan pesanan.</p>
    </div>
</div>

@if ($errors->any())
    <div class="notice notice-bad" role="alert" style="margin:0 0 20px">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form class="form-box" method="POST" action="{{ route('settings.payment.update') }}" enctype="multipart/form-data">
    @csrf

    <fieldset class="fieldset">
        <legend>QRIS</legend>
        <div class="check">
            <input type="checkbox" id="qris_is_active" name="qris_is_active" value="1" @checked(old('qris_is_active', $setting->qris_is_active))>
            <label for="qris_is_active">Tampilkan QRIS ke pembeli</label>
        </div>

        @if ($setting->qris_image)
            <p class="field-hint" style="margin-bottom:8px">QRIS saat ini</p>
            <img id="qris-preview" class="preview" src="{{ asset('storage/'.$setting->qris_image) }}" width="200" height="200" alt="QRIS yang sedang dipakai">
        @else
            <img id="qris-preview" class="preview" src="" width="200" height="200" alt="Pratinjau QRIS baru" hidden>
        @endif

        <div class="field" style="margin:0">
            <label for="qris_image">{{ $setting->qris_image ? 'Ganti gambar QRIS' : 'Unggah gambar QRIS' }}</label>
            <input class="input" type="file" id="qris_image" name="qris_image" accept="image/*" aria-describedby="qris-hint">
            <p class="field-hint" id="qris-hint">Format gambar, maksimal 2 MB. QRIS tampil ke pembeli hanya jika aktif dan gambar sudah ada.</p>
        </div>
    </fieldset>

    <fieldset class="fieldset">
        <legend>Transfer BCA</legend>
        <div class="check">
            <input type="checkbox" id="bca_is_active" name="bca_is_active" value="1" @checked(old('bca_is_active', $setting->bca_is_active))>
            <label for="bca_is_active">Tampilkan transfer BCA ke pembeli</label>
        </div>
        <div class="cols-2">
            <div class="field">
                <label for="bca_account_number">Nomor rekening</label>
                <input class="input" type="text" inputmode="numeric" id="bca_account_number" name="bca_account_number" value="{{ old('bca_account_number', $setting->bca_account_number) }}" placeholder="1234567890" aria-describedby="bca-hint">
            </div>
            <div class="field">
                <label for="bca_account_name">Nama pemilik rekening</label>
                <input class="input" type="text" id="bca_account_name" name="bca_account_name" value="{{ old('bca_account_name', $setting->bca_account_name) }}" placeholder="Nama sesuai buku tabungan">
            </div>
        </div>
        <p class="field-hint" id="bca-hint">Kosongkan nomor rekening kalau opsi transfer BCA belum ingin ditampilkan.</p>
    </fieldset>

    <div class="field">
        <label for="notes">Catatan atau instruksi pembayaran</label>
        <textarea class="input" id="notes" name="notes" rows="4" maxlength="1000" aria-describedby="notes-hint" placeholder="Contoh: Transfer sesuai nominal pesanan, lalu kirim bukti ke WhatsApp kami.">{{ old('notes', $setting->notes) }}</textarea>
        <p class="field-hint" id="notes-hint">Tampil untuk semua metode pembayaran yang aktif.</p>
    </div>

    <button type="submit" class="btn btn-primary" data-busy="Menyimpan...">Simpan Pengaturan</button>
</form>
@endsection

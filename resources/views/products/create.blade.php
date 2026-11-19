@extends('layouts.dashboard')

@section('title', 'Tambah Produk')

@section('content')
<div class="pg-head">
    <div>
        <h1>Tambah produk</h1>
        <p>Template voucher baru untuk katalog.</p>
    </div>
</div>
<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" style="max-width:960px">
    @include('products._form')
</form>
@endsection

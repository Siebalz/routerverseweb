@extends('layouts.dashboard')

@section('title', 'Edit Produk')

@section('content')
<div class="pg-head">
    <div>
        <h1>Edit produk</h1>
        <p>{{ $product->name }}</p>
    </div>
</div>
<form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" style="max-width:960px">
    @method('PUT')
    @include('products._form')
</form>
@endsection

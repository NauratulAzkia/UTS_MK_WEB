@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="page-heading"><div><p class="eyebrow">DATA MASTER</p><h1>{{ $title }}</h1><p class="muted">Informasi detail data.</p></div><a class="button ghost" href="{{ route($type.'.index') }}">← Kembali</a></div>
<section class="panel detail-panel">
    @if($type === 'products')
        <dl class="detail-grid"><dt>SKU</dt><dd>{{ $row->sku }}</dd><dt>Nama produk</dt><dd>{{ $row->name }}</dd><dt>Kategori</dt><dd>{{ $row->category->name }}</dd><dt>Harga beli</dt><dd>Rp {{ number_format($row->purchase_price, 0, ',', '.') }}</dd><dt>Harga jual</dt><dd>Rp {{ number_format($row->selling_price, 0, ',', '.') }}</dd><dt>Stok</dt><dd>{{ $row->stock }} unit</dd><dt>Stok minimum</dt><dd>{{ $row->minimum_stock }} unit</dd></dl>
    @elseif($type === 'suppliers')
        <dl class="detail-grid"><dt>Nama</dt><dd>{{ $row->name }}</dd><dt>Telepon</dt><dd>{{ $row->phone ?: '—' }}</dd><dt>Email</dt><dd>{{ $row->email ?: '—' }}</dd></dl>
    @else
        <dl class="detail-grid"><dt>Nama kategori</dt><dd>{{ $row->name }}</dd><dt>Jumlah produk</dt><dd>{{ $row->products->count() }}</dd></dl>
        @if($row->products->isNotEmpty())<h2 class="subheading">Produk dalam kategori</h2><ul class="plain-list">@foreach($row->products as $product)<li><a href="{{ route('products.show', $product) }}">{{ $product->sku }} · {{ $product->name }}</a></li>@endforeach</ul>@endif
    @endif
</section>
@endsection

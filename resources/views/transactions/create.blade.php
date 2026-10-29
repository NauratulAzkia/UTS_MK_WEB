@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="page-heading"><div><p class="eyebrow">TRANSAKSI BARU</p><h1>{{ $title }}</h1><p class="muted">Nilai transaksi dihitung ulang oleh server saat disimpan.</p></div><a class="button ghost" href="{{ route($type.'.index') }}">← Kembali</a></div>
<form method="POST" action="{{ route($type.'.store') }}" class="transaction-form">
    @csrf
    @if($type === 'purchases')
        <section class="panel form-panel"><h2>Informasi pemasok</h2><label>Pemasok<select name="supplier_id" required><option value="">Pilih pemasok</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></label></section>
    @endif
    <section class="panel form-panel"><div class="panel-heading"><div><h2>Item transaksi</h2><p class="muted">Pilih produk dan kuantitas setiap item.</p></div><button class="button secondary" type="button" id="add-item">＋ Tambah item</button></div>
        <div id="items">
            @php($oldItems = old('items', [['product_id' => '', 'quantity' => 1, 'unit_cost' => '']]))
            @foreach($oldItems as $index => $item)
            <div class="transaction-line">
                <label class="product-field">Produk<select name="items[{{ $index }}][product_id]" required><option value="">Pilih produk</option>@foreach($products as $product)<option value="{{ $product->id }}" data-cost="{{ $product->purchase_price }}" data-price="{{ $product->selling_price }}" data-stock="{{ $product->stock }}" @selected(($item['product_id'] ?? '') == $product->id)>{{ $product->sku }} — {{ $product->name }} (stok {{ $product->stock }})</option>@endforeach</select></label>
                <label>Jumlah<input type="number" min="1" max="1000000" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required></label>
                @if($type === 'purchases')<label>Harga beli/unit<input type="number" min="0" step="0.01" name="items[{{ $index }}][unit_cost]" value="{{ $item['unit_cost'] ?? '' }}" required></label>
                @else<label>Harga jual/unit <small>otomatis dari data produk</small><input type="text" class="price-preview" value="—" readonly></label>@endif
                <button class="remove-line" type="button" aria-label="Hapus item" title="Hapus item">×</button>
            </div>
            @endforeach
        </div>
        <div class="form-actions"><a class="button ghost" href="{{ route($type.'.index') }}">Batal</a><button class="button primary" type="submit">Simpan {{ $type === 'purchases' ? 'pembelian' : 'penjualan' }}</button></div>
    </section>
</form>
<template id="item-template"><div class="transaction-line">
    <label class="product-field">Produk<select required><option value="">Pilih produk</option>@foreach($products as $product)<option value="{{ $product->id }}" data-cost="{{ $product->purchase_price }}" data-price="{{ $product->selling_price }}" data-stock="{{ $product->stock }}">{{ $product->sku }} — {{ $product->name }} (stok {{ $product->stock }})</option>@endforeach</select></label>
    <label>Jumlah<input type="number" min="1" max="1000000" value="1" required></label>
    @if($type === 'purchases')<label>Harga beli/unit<input type="number" min="0" step="0.01" required></label>
    @else<label>Harga jual/unit <small>otomatis dari data produk</small><input type="text" class="price-preview" value="—" readonly></label>@endif
    <button class="remove-line" type="button" aria-label="Hapus item" title="Hapus item">×</button>
</div></template>
@endsection
@push('scripts')
<script>
(() => {
    const list = document.getElementById('items');
    let nextIndex = {{ count($oldItems) }};
    const purchase = @json($type === 'purchases');
    const template = document.getElementById('item-template');
    const updatePreview = (line, prefillCost = false) => {
        const select = line.querySelector('select');
        const option = select.options[select.selectedIndex];
        const preview = line.querySelector('.price-preview');
        if (preview) preview.value = option && option.value ? `Rp ${Number(option.dataset.price).toLocaleString('id-ID')}` : '—';
        if (purchase && prefillCost && option && option.value) line.querySelectorAll('input')[1].value = option.dataset.cost;
    };
    const bind = (line, index) => {
        const select = line.querySelector('select');
        const inputs = line.querySelectorAll('input');
        select.name = `items[${index}][product_id]`;
        inputs[0].name = `items[${index}][quantity]`;
        if (purchase) inputs[1].name = `items[${index}][unit_cost]`;
        select.addEventListener('change', () => updatePreview(line, true));
        line.querySelector('.remove-line').addEventListener('click', () => {
            if (list.children.length > 1) line.remove();
        });
        updatePreview(line);
    };
    Array.from(list.children).forEach((line, index) => bind(line, index));
    document.getElementById('add-item').addEventListener('click', () => {
        const line = template.content.firstElementChild.cloneNode(true);
        list.appendChild(line);
        bind(line, nextIndex++);
    });
})();
</script>
@endpush

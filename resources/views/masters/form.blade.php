@extends('layouts.app')
@section('title', ($row->exists ? 'Edit ' : 'Tambah ').$title)
@section('content')
<div class="page-heading"><div><p class="eyebrow">DATA MASTER</p><h1>{{ $row->exists ? 'Edit' : 'Tambah' }} {{ $title }}</h1><p class="muted">Lengkapi informasi berikut dengan benar.</p></div><a class="button ghost" href="{{ route($type.'.index') }}">← Kembali</a></div>
<section class="panel form-panel"><form method="POST" action="{{ $row->exists ? route($type.'.update', $row) : route($type.'.store') }}" class="stack-form">
    @csrf @if($row->exists)@method('PUT')@endif
    @if($type === 'categories')
        <label>Nama kategori<input name="name" value="{{ old('name', $row->name) }}" required maxlength="255"></label>
    @elseif($type === 'products')
        <div class="form-grid">
            <label>Kategori<select name="category_id" required><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $row->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label>SKU<input name="sku" value="{{ old('sku', $row->sku) }}" required maxlength="100"></label>
            <label>Nama produk<input name="name" value="{{ old('name', $row->name) }}" required maxlength="255"></label>
            <label>Harga beli<input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $row->purchase_price) }}" required></label>
            <label>Harga jual<input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price', $row->selling_price) }}" required></label>
            <label>{{ $row->exists ? 'Stok saat ini' : 'Stok awal' }}<input type="number" min="0" @if(!$row->exists) name="stock" required @else readonly @endif value="{{ old('stock', $row->stock ?? 0) }}"></label>
            <label>Stok minimum<input type="number" min="0" name="minimum_stock" value="{{ old('minimum_stock', $row->minimum_stock ?? 0) }}" required></label>
        </div><p class="form-note">Untuk perubahan stok operasional setelah produk dibuat, gunakan transaksi pembelian atau penjualan.</p>
    @elseif($type === 'suppliers')
        <label>Nama pemasok<input name="name" value="{{ old('name', $row->name) }}" required maxlength="255"></label>
        <div class="form-grid"><label>Nomor telepon<input name="phone" value="{{ old('phone', $row->phone) }}" maxlength="40"></label><label>Email<input type="email" name="email" value="{{ old('email', $row->email) }}"></label></div>
    @else
        <label>Nama<input name="name" value="{{ old('name', $row->name) }}" required maxlength="255"></label>
        <label>Email<input type="email" name="email" value="{{ old('email', $row->email) }}" required></label>
        <label>Password<input type="password" name="password" {{ $row->exists ? '' : 'required' }} minlength="8" autocomplete="new-password"><small>{{ $row->exists ? 'Kosongkan jika tidak ingin mengganti password.' : 'Minimal 8 karakter.' }}</small></label>
        <label>Peran<select name="role" required><option value="staff" @selected(old('role', $row->role ?? 'staff') === 'staff')>Staff</option><option value="customer" @selected(old('role', $row->role) === 'customer')>Pelanggan</option><option value="admin" @selected(old('role', $row->role) === 'admin')>Admin / Owner</option></select></label>
    @endif
    <div class="form-actions"><a class="button ghost" href="{{ route($type.'.index') }}">Batal</a><button class="button primary" type="submit">Simpan {{ strtolower($title) }}</button></div>
</form></section>
@endsection

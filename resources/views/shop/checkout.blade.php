@extends('shop.layout')
@section('title', 'Buat pesanan')
@section('content')
<div class="store-page-heading"><div><p class="eyebrow">PAW PANTRY ONLINE</p><h1>Checkout pesanan</h1><p class="muted">Pilih jumlah produk lalu lengkapi informasi pengiriman.</p></div><a class="button ghost" href="{{ route('shop.index') }}">← Kembali ke katalog</a></div>
<form method="POST" action="{{ route('shop.checkout.store') }}" class="checkout-grid">
    @csrf
    <section class="checkout-products">
        <div class="checkout-section-heading"><div><h2>Produk tersedia</h2><p class="muted">Isi jumlah yang ingin dipesan. Stok akan dicek kembali saat pesanan dikirim.</p></div></div>
        @forelse($products as $product)
            <label class="checkout-product">
                <span class="checkout-product-mark">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                <span class="checkout-product-info"><strong>{{ $product->name }}</strong><small>{{ $product->category->name }} · {{ $product->stock }} tersedia</small></span>
                <span class="checkout-price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                <input type="number" min="0" max="{{ $product->stock }}" name="items[{{ $product->id }}]" value="{{ old('items.'.$product->id, $selectedProduct === $product->id ? 1 : 0) }}" aria-label="Jumlah {{ $product->name }}">
            </label>
        @empty
            <div class="catalog-empty"><h3>Stok sedang kosong</h3><p>Silakan cek kembali nanti.</p></div>
        @endforelse
    </section>
    <aside class="checkout-details">
        <div class="checkout-section-heading"><div><h2>Informasi pengiriman</h2><p class="muted">Pastikan nomor telepon dan alamat aktif.</p></div></div>
        <label>Nama penerima<input value="{{ auth()->user()->name }}" readonly></label>
        <label>Email akun<input value="{{ auth()->user()->email }}" readonly></label>
        <label>Nomor telepon<input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required maxlength="40" autocomplete="tel" placeholder="Contoh: 081234567890"></label>
        <label>Alamat lengkap<textarea name="shipping_address" rows="4" required maxlength="2000" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos">{{ old('shipping_address') }}</textarea></label>
        <div class="checkout-note"><strong>Informasi pembayaran</strong><p>Pesanan dicatat sebagai transaksi toko. Konfirmasi pembayaran dan pengiriman dilakukan oleh pihak toko.</p></div>
        <button class="button primary checkout-submit" type="submit">Buat pesanan <span>→</span></button>
        <p class="checkout-safe">Total dihitung dari harga produk di sistem saat pesanan diproses.</p>
    </aside>
</form>
@endsection

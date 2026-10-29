@extends('shop.layout')
@section('title', 'Katalog makanan kucing')
@section('content')
<section class="shop-hero">
    <div>
        <span class="shop-kicker">PAW PANTRY ONLINE</span>
        <h1>Favorit si meong,<br>tinggal pilih.</h1>
        <p>Temukan makanan harian dan nutrisi pilihan dari merek terpercaya.</p>
        <a class="shop-hero-link" href="#katalog">Lihat katalog <span>↓</span></a>
    </div>
    <div class="shop-hero-art" aria-hidden="true"><span class="hero-cat">🐈</span><span class="hero-bowl">PAW</span><i>purr<br>fect!</i></div>
</section>
<section id="katalog" class="catalog-section">
    <div class="catalog-heading">
        <div><p class="eyebrow">PILIHAN PAW PANTRY</p><h2>Katalog produk</h2><p class="muted">Harga ditampilkan dalam Rupiah dan stok mengikuti ketersediaan toko.</p></div>
        <span class="catalog-count">{{ $products->total() }} produk tersedia</span>
    </div>
    <form method="GET" action="{{ route('shop.index') }}" class="catalog-filter">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari merek atau nama produk..." aria-label="Cari produk">
        <select name="category_id" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button class="button primary" type="submit">Cari produk</button>
        @if(request()->hasAny(['q', 'category_id']))<a class="catalog-reset" href="{{ route('shop.index') }}">Reset</a>@endif
    </form>
    <div class="catalog-grid">
        @forelse($products as $product)
            <article class="product-card">
                <div class="product-art product-art-{{ $loop->index % 5 }}"><span>{{ strtoupper(substr($product->name, 0, 1)) }}</span><small>{{ $product->category->name }}</small><i>🐾</i></div>
                <div class="product-card-body">
                    <span class="product-brand">{{ explode(' ', $product->name)[0] }}</span>
                    <h3>{{ $product->name }}</h3>
                    <div class="product-meta"><span>SKU {{ $product->sku }}</span><span>Stok {{ $product->stock }}</span></div>
                    <div class="product-card-bottom"><strong>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</strong>
                        @auth
                            @if(auth()->user()->role === 'customer')
                                <a class="button primary product-order" href="{{ route('shop.checkout', ['product' => $product->id]) }}">Pesan</a>
                            @else
                                <span class="stock-label">Tersedia</span>
                            @endif
                        @else
                            <a class="button primary product-order" href="{{ route('shop.checkout', ['product' => $product->id]) }}">Pesan</a>
                        @endauth
                    </div>
                </div>
            </article>
        @empty
            <div class="catalog-empty"><span>♡</span><h3>Produk belum ditemukan</h3><p>Coba ubah kata pencarian atau pilih kategori lain.</p></div>
        @endforelse
    </div>
    <div class="catalog-pagination">{{ $products->links() }}</div>
</section>
<aside class="price-notice">Harga dan ketersediaan dapat berubah. Produk diet veteriner sebaiknya digunakan sesuai saran dokter hewan.</aside>
@endsection

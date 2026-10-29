@extends('shop.layout')
@section('title', 'Riwayat pesanan')
@section('content')
<div class="store-page-heading"><div><p class="eyebrow">AKUN PELANGGAN</p><h1>Riwayat pembelian</h1><p class="muted">Halo, {{ auth()->user()->name }}. Berikut pesanan yang pernah Anda buat.</p></div><a class="button primary" href="{{ route('shop.index') }}">Belanja lagi →</a></div>
<section class="orders-list">
    @forelse($orders as $order)
        <article class="order-card">
            <div class="order-main">
                <span class="order-icon">🐾</span>
                <div><span class="order-number">{{ $order->sale_number }}</span><strong>{{ $order->sold_at->format('d M Y · H:i') }}</strong><small>{{ $order->items_count }} jenis produk · {{ $order->status === 'completed' ? 'Pesanan dicatat' : 'Dibatalkan' }}</small></div>
            </div>
            <div class="order-total"><small>Total pesanan</small><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></div>
            <a class="button secondary" href="{{ route('shop.orders.invoice', $order) }}">Lihat faktur</a>
        </article>
    @empty
        <div class="orders-empty"><span>♡</span><h2>Belum ada pesanan</h2><p>Produk yang Anda pesan akan tercatat di halaman ini.</p><a class="button primary" href="{{ route('shop.index') }}">Mulai belanja</a></div>
    @endforelse
    <div class="catalog-pagination">{{ $orders->links() }}</div>
</section>
@endsection

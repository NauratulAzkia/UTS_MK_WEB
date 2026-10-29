@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="page-heading dashboard-heading">
    <div><p class="eyebrow">PAW PANTRY · RINGKASAN HARIAN</p><h1>Halo, {{ auth()->user()->name }}!</h1><p class="muted">{{ now()->locale('id')->translatedFormat('l, d F Y') }} · Siap jaga stok si meong tetap lengkap?</p></div>
</div>
<section class="welcome-banner">
    <div class="welcome-copy">
        <span class="welcome-tag">CAT FOOD STOCKROOM</span>
        <h2>Stok rapi,<br>kucing happy.</h2>
        <p>Pantau produk favorit, cek stok yang menipis, dan layani pesanan lebih cepat.</p>
        <div class="welcome-actions">
            <a class="button banner-button" href="{{ route('sales.create') }}">＋ Buat penjualan</a>
            <a class="banner-link" href="{{ route('products.index') }}">Jelajahi katalog <span>→</span></a>
        </div>
    </div>
    <div class="welcome-art" aria-hidden="true">
        <span class="art-spark spark-one">✦</span><span class="art-spark spark-two">✦</span>
        <div class="cat-disc"><span>🐈</span></div>
        <div class="food-bowl"><span>PAW</span><i></i></div>
        <span class="art-caption">good food<br>good mood</span>
    </div>
</section>
<div class="stats-grid paw-stats">
    <article class="stat-card paw-stat"><span class="stat-icon green">Rp</span><p>Penjualan hari ini</p><strong>Rp {{ number_format($todaySales, 0, ',', '.') }}</strong><small>Transaksi selesai hari ini</small></article>
    <article class="stat-card paw-stat"><span class="stat-icon orange">▤</span><p>Produk dalam katalog</p><strong>{{ number_format($productCount) }}</strong><small>{{ number_format($categoryCount) }} kategori makanan</small></article>
    <article class="stat-card paw-stat"><span class="stat-icon rose">!</span><p>Perlu restock</p><strong>{{ number_format($lowStockCount) }}</strong><small>Produk pada/bawah stok minimum</small></article>
    <article class="stat-card paw-stat"><span class="stat-icon lilac">↗</span><p>Penjualan bulan ini</p><strong>Rp {{ number_format($monthSales, 0, ',', '.') }}</strong><small>{{ number_format($transactionCount) }} transaksi tercatat</small></article>
</div>
<div class="dashboard-grid">
    <section class="panel trend-panel">
        <div class="panel-heading"><div><h2>Aktivitas penjualan</h2><p class="muted">Pergerakan 7 hari terakhir</p></div><span class="trend-total">Rp {{ number_format((float) $monthlyReport->sum('total'), 0, ',', '.') }}<small>total 7 hari</small></span></div>
        @php($maxDailySales = max((float) $monthlyReport->max('total'), 1))
        @forelse($monthlyReport as $day)
            <div class="trend-row"><span>{{ \Illuminate\Support\Carbon::parse($day->day)->translatedFormat('D, d M') }}</span><div class="trend-track"><i style="width: {{ max(4, ((float) $day->total / $maxDailySales) * 100) }}%"></i></div><strong>Rp {{ number_format((float) $day->total, 0, ',', '.') }}</strong></div>
        @empty
            <div class="chart-empty"><span>♡</span><strong>Belum ada penjualan minggu ini</strong><small>Transaksi baru akan muncul di ringkasan ini.</small></div>
        @endforelse
    </section>
    <section class="panel restock-panel">
        <div class="panel-heading"><div><h2>Waktunya restock</h2><p class="muted">{{ number_format($lowStockCount) }} produk butuh perhatian</p></div><a class="text-link" href="{{ route('products.index', ['low_stock' => 1]) }}">Lihat semua →</a></div>
        @forelse($lowStock as $product)
            <div class="list-row restock-row"><div class="item-symbol food-symbol">{{ strtoupper(substr($product->name, 0, 1)) }}</div><div class="row-grow"><strong>{{ $product->name }}</strong><small>{{ $product->sku }} · {{ $product->category->name }}</small></div><span class="badge warning">{{ $product->stock }} <small>/ {{ $product->minimum_stock }}</small></span></div>
        @empty
            <div class="chart-empty"><span>✓</span><strong>Semua stok aman</strong><small>Belum ada produk di bawah batas minimum.</small></div>
        @endforelse
    </section>
</div>
<div class="dashboard-grid bottom-grid">
    <section class="panel">
        <div class="panel-heading"><div><h2>Penjualan terbaru</h2><p class="muted">Pesanan yang baru diproses</p></div><a class="text-link" href="{{ route('sales.index') }}">Semua penjualan →</a></div>
        @forelse($recentSales as $sale)
            <a class="list-row plain-row" href="{{ route('sales.show', $sale) }}"><div class="item-symbol green-symbol">↗</div><div class="row-grow"><strong>{{ $sale->sale_number }}</strong><small>{{ $sale->user->name }} · {{ $sale->sold_at->format('d M, H:i') }}</small></div><div class="row-amount">Rp {{ number_format($sale->total, 0, ',', '.') }}<small>{{ $sale->status }}</small></div></a>
        @empty <div class="empty-state">Belum ada transaksi penjualan.</div> @endforelse
    </section>
    <section class="panel">
        <div class="panel-heading"><div><h2>Pembelian stok</h2><p class="muted">Barang masuk dari pemasok</p></div><a class="text-link" href="{{ route('purchases.index') }}">Semua pembelian →</a></div>
        @forelse($recentPurchases as $purchase)
            <a class="list-row plain-row" href="{{ route('purchases.show', $purchase) }}"><div class="item-symbol orange-symbol">↘</div><div class="row-grow"><strong>{{ $purchase->purchase_number }}</strong><small>{{ $purchase->supplier->name }} · {{ $purchase->purchased_at->format('d M, H:i') }}</small></div><div class="row-amount">Rp {{ number_format($purchase->total, 0, ',', '.') }}<small>{{ $purchase->status }}</small></div></a>
        @empty <div class="empty-state">Belum ada transaksi pembelian.</div> @endforelse
    </section>
</div>
@endsection

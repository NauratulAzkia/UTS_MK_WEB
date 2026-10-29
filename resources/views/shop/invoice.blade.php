@extends('shop.layout')
@section('title', 'Faktur '.$order->sale_number)
@section('content')
<div class="invoice-toolbar no-print"><a class="button ghost" href="{{ route('shop.orders.index') }}">← Riwayat pembelian</a><button class="button primary" type="button" onclick="window.print()">Cetak faktur</button></div>
<article class="invoice">
    <header class="invoice-header">
        <div><a class="store-brand invoice-brand" href="{{ route('shop.index') }}"><span class="brand-mark">P</span><span>Paw Pantry<small>Makanan baik untuk si meong</small></span></a><p class="invoice-store-note">Bukti pesanan pelanggan</p></div>
        <div class="invoice-heading"><span>FAKTUR</span><strong>{{ $order->sale_number }}</strong><small>{{ $order->sold_at->format('d F Y, H:i') }}</small></div>
    </header>
    <div class="invoice-parties">
        <div><span>DITAGIHKAN KEPADA</span><strong>{{ $order->user->name }}</strong><small>{{ $order->user->email }}</small><small>{{ $order->customer_phone }}</small></div>
        <div><span>ALAMAT PENGIRIMAN</span><p>{{ $order->shipping_address }}</p></div>
    </div>
    <div class="invoice-table-wrap"><table class="invoice-table"><thead><tr><th>Produk</th><th>Harga satuan</th><th>Jumlah</th><th>Subtotal</th></tr></thead><tbody>
        @foreach($order->items as $item)
            <tr><td><strong>{{ $item->product->name }}</strong><small>SKU {{ $item->product->sku }}</small></td><td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td><td>{{ $item->quantity }}</td><td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td></tr>
        @endforeach
    </tbody></table></div>
    <div class="invoice-total"><span>Total pesanan</span><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></div>
    <footer class="invoice-footer"><span>Status: {{ $order->status === 'completed' ? 'Pesanan dicatat' : 'Dibatalkan' }}</span><p>Terima kasih telah berbelanja di Paw Pantry. Simpan faktur ini sebagai bukti pesanan.</p></footer>
</article>
@endsection

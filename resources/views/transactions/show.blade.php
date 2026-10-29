@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="page-heading"><div><p class="eyebrow">DETAIL TRANSAKSI</p><h1>{{ $type === 'purchases' ? $row->purchase_number : $row->sale_number }}</h1><p class="muted">{{ $type === 'purchases' ? 'Pembelian / barang masuk' : 'Penjualan' }}</p></div><div class="heading-actions"><a class="button ghost" href="{{ route($type.'.index') }}">← Kembali</a>
    @if(auth()->user()->isAdmin() && $row->status === 'completed')
        <form method="POST" action="{{ route($type.'.cancel', $row) }}" onsubmit="return confirm('Pembatalan akan membalik perubahan stok. Lanjutkan?')">@csrf<button class="button danger" type="submit">Batalkan transaksi</button></form>
    @endif
</div></div>
<section class="panel transaction-summary">
    <div><small>Status</small><span class="badge {{ $row->status === 'completed' ? 'success-badge' : 'danger-badge' }}">{{ $row->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span></div>
    <div><small>{{ $type === 'purchases' ? 'Pemasok' : 'Dibuat oleh' }}</small><strong>{{ $type === 'purchases' ? $row->supplier->name : $row->user->name }}</strong></div>
    @if($type === 'purchases')<div><small>Petugas</small><strong>{{ $row->user->name }}</strong></div>@endif
    <div><small>Tanggal</small><strong>{{ ($type === 'purchases' ? $row->purchased_at : $row->sold_at)->format('d F Y, H:i') }}</strong></div>
</section>
<section class="panel table-panel"><div class="panel-heading"><div><h2>Rincian item</h2><p class="muted">Harga dan subtotal yang tersimpan saat transaksi.</p></div></div>
    <div class="table-wrap"><table><thead><tr><th>Produk</th><th>SKU</th><th>Jumlah</th><th>Harga/unit</th><th>Subtotal</th></tr></thead><tbody>
    @foreach($row->items as $item)<tr><td>{{ $item->product->name }}</td><td>{{ $item->product->sku }}</td><td>{{ $item->quantity }}</td><td>Rp {{ number_format($type === 'purchases' ? $item->unit_cost : $item->unit_price, 0, ',', '.') }}</td><td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td></tr>@endforeach
    </tbody><tfoot><tr><th colspan="4">Total</th><th>Rp {{ number_format($row->total, 0, ',', '.') }}</th></tr></tfoot></table></div>
</section>
@endsection

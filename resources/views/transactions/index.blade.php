@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="page-heading"><div><p class="eyebrow">TRANSAKSI</p><h1>{{ $title }}</h1><p class="muted">Riwayat transaksi {{ strtolower($title) }} dan status stok.</p></div><a class="button primary" href="{{ route($type.'.create') }}">＋ Transaksi baru</a></div>
<section class="panel table-panel">
    <form method="GET" class="filter-bar">
        <label>Dari<input type="date" name="from" value="{{ request('from') }}"></label>
        <label>Sampai<input type="date" name="to" value="{{ request('to') }}"></label>
        <select name="status"><option value="">Semua status</option><option value="completed" @selected(request('status') === 'completed')>Selesai</option><option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option></select>
        <button class="button secondary" type="submit">Filter</button><a class="button ghost" href="{{ route($type.'.index') }}">Reset</a>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Nomor transaksi</th>@if($type === 'purchases')<th>Pemasok</th>@endif<th>Pengguna</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    @forelse($rows as $row)
        <tr><td><strong>{{ $type === 'purchases' ? $row->purchase_number : $row->sale_number }}</strong></td>
            @if($type === 'purchases')<td>{{ $row->supplier->name }}</td>@endif
            <td>{{ $row->user->name }}</td><td>{{ $type === 'purchases' ? $row->purchased_at->format('d/m/Y H:i') : $row->sold_at->format('d/m/Y H:i') }}</td>
            <td>Rp {{ number_format($row->total, 0, ',', '.') }}</td><td><span class="badge {{ $row->status === 'completed' ? 'success-badge' : 'danger-badge' }}">{{ $row->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span></td>
            <td class="actions"><a class="action-link" href="{{ route($type.'.show', $row) }}">Detail</a></td>
        </tr>
    @empty<tr><td colspan="{{ $type === 'purchases' ? 7 : 6 }}"><div class="empty-state">Belum ada transaksi yang sesuai.</div></td></tr>@endforelse
    </tbody></table></div><div class="pagination-wrap">{{ $rows->links() }}</div>
</section>
@endsection

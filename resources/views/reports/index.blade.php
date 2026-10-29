@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<div class="page-heading"><div><p class="eyebrow">ANALISIS</p><h1>Laporan penjualan</h1><p class="muted">Ringkasan dari transaksi penjualan yang selesai.</p></div></div>
<section class="panel table-panel">
    <form method="GET" class="filter-bar"><label>Dari<input type="date" name="from" value="{{ $from }}"></label><label>Sampai<input type="date" name="to" value="{{ $to }}"></label><button class="button secondary" type="submit">Terapkan</button><a class="button ghost" href="{{ route('reports.index') }}">Reset</a></form>
    <div class="report-cards"><article class="stat-card"><p>Total penjualan</p><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong><small>Dalam periode terpilih</small></article><article class="stat-card"><p>Transaksi selesai</p><strong>{{ number_format($count) }}</strong><small>Dalam periode terpilih</small></article></div>
    <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Jumlah transaksi</th><th>Total penjualan</th></tr></thead><tbody>@forelse($rows as $row)<tr><td>{{ \Illuminate\Support\Carbon::parse($row->day)->format('d F Y') }}</td><td>{{ $row->transactions }}</td><td>Rp {{ number_format($row->total, 0, ',', '.') }}</td></tr>@empty<tr><td colspan="3"><div class="empty-state">Tidak ada data penjualan pada periode ini.</div></td></tr>@endforelse</tbody></table></div><div class="pagination-wrap">{{ $rows->links() }}</div>
</section>
@endsection

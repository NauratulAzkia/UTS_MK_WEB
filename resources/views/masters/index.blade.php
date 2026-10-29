@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="page-heading"><div><p class="eyebrow">DATA MASTER</p><h1>{{ $title }}</h1><p class="muted">Kelola informasi {{ strtolower($title) }} dalam sistem.</p></div>
    @if(auth()->user()->isAdmin())<a class="button primary" href="{{ route($type.'.create') }}">＋ Tambah {{ $title }}</a>@endif
</div>
<section class="panel table-panel">
    @if(!empty($filters))
    <form method="GET" class="filter-bar">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama atau SKU...">
        <select name="category_id"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>
        <label class="check"><input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock'))> Stok rendah</label>
        <button class="button secondary" type="submit">Terapkan</button><a class="button ghost" href="{{ route('products.index') }}">Reset</a>
    </form>
    @endif
    <div class="table-wrap"><table><thead><tr>@foreach($columns as $column)<th>{{ $column }}</th>@endforeach<th>Aksi</th></tr></thead><tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($columns as $key => $label)
                    @php($value = data_get($row, $key))
                    <td>@if($key === 'selling_price')Rp {{ number_format((float) $value, 0, ',', '.') }}
                        @elseif($key === 'role')<span class="badge {{ $value === 'admin' ? 'blue-badge' : ($value === 'customer' ? 'success-badge' : '') }}">{{ match($value) { 'admin' => 'Admin / Owner', 'customer' => 'Pelanggan', default => 'Staff' } }}</span>
                        @elseif($key === 'stock')<span class="badge {{ $row->stock <= $row->minimum_stock ? 'warning' : 'success-badge' }}">{{ $value }}</span>
                        @else{{ $value ?? '—' }}@endif
                    </td>
                @endforeach
                <td class="actions">
                    @if(Route::has($type.'.show'))<a class="action-link" href="{{ route($type.'.show', $row) }}">Detail</a>@endif
                    @if(auth()->user()->isAdmin())
                        <a class="action-link" href="{{ route($type.'.edit', $row) }}">Edit</a>
                        <form method="POST" action="{{ route($type.'.destroy', $row) }}" onsubmit="return confirm('Yakin ingin menghapus data ini?')">@csrf @method('DELETE')<button class="action-link danger-link" type="submit">Hapus</button></form>
                    @endif
                </td>
            </tr>
        @empty<tr><td colspan="{{ count($columns) + 1 }}"><div class="empty-state">Belum ada data untuk ditampilkan.</div></td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination-wrap">{{ $rows->links() }}</div>
</section>
@endsection

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · Paw Pantry</title>
    @vite(['resources/css/app.css', 'resources/css/inventory.css', 'resources/css/paw-pantry.css'])
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">P</span><span>Paw Pantry<small>Cat food inventory</small></span></a>
        <div class="nav-label">TOKO &amp; STOK</div>
        <nav>
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span>Dashboard</span></a>
            <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><span>Produk</span></a>
            <a class="{{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><span>Kategori</span></a>
            <a class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}"><span>Pemasok</span></a>
            <a class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}"><span>Pembelian</span></a>
            <a class="{{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}"><span>Penjualan</span></a>
            <a class="{{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><span>Laporan</span></a>
            @if(auth()->user()->isAdmin())
                <div class="nav-label nav-gap">PENGELOLAAN</div>
                <a class="{{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><span>Pengguna</span></a>
            @endif
        </nav>
    </aside>
    <main class="main">
        <header class="topbar">
            <div class="mobile-brand">Paw Pantry</div>
            <div class="breadcrumb">Workspace <span>/</span> <strong>@yield('title', 'Dashboard')</strong></div>
            <div class="profile"><div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div><div><strong>{{ auth()->user()->name }}</strong><small>{{ match(auth()->user()->role) { 'admin' => 'Admin / Owner', 'customer' => 'Pelanggan', default => 'Staff' } }}</small></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Keluar</button></form>
            </div>
        </header>
        <section class="content">
            @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert error"><strong>Periksa kembali:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </section>
    </main>
</div>
@stack('scripts')
</body>
</html>

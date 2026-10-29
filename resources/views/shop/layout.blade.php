<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Toko Online') · Paw Pantry</title>
    @vite(['resources/css/app.css', 'resources/css/paw-pantry.css', 'resources/css/storefront.css'])
</head>
<body class="storefront-body">
<header class="store-header">
    <a class="store-brand" href="{{ route('shop.index') }}"><span class="brand-mark">P</span><span>Paw Pantry<small>Makanan baik untuk si meong</small></span></a>
    <nav class="store-nav" aria-label="Navigasi toko">
        <a class="{{ request()->routeIs('shop.index') ? 'active' : '' }}" href="{{ route('shop.index') }}">Katalog</a>
        @auth
            @if(auth()->user()->role === 'customer')
                <a class="{{ request()->routeIs('shop.orders.*') ? 'active' : '' }}" href="{{ route('shop.orders.index') }}">Pesanan saya</a>
                <a class="store-order-link" href="{{ route('shop.checkout') }}">Checkout</a>
            @else
                <a href="{{ route('dashboard') }}">Panel toko</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="store-nav-button">Keluar</button></form>
        @else
            <a href="{{ route('login') }}">Masuk</a>
            <a class="store-order-link" href="{{ route('register') }}">Daftar</a>
        @endauth
    </nav>
</header>
<main class="store-main">
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert error"><strong>Periksa kembali pesanan:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer class="store-footer">Paw Pantry <span>·</span> Teman belanja nutrisi kucing kesayangan.</footer>
</body>
</html>

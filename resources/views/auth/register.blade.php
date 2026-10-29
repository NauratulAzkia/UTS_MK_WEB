<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar pelanggan · Paw Pantry</title>
    @vite(['resources/css/app.css', 'resources/css/inventory.css', 'resources/css/paw-pantry.css', 'resources/css/storefront.css'])
</head>
<body class="login-page">
<main class="login-card">
    <a class="brand login-brand" href="{{ route('shop.index') }}"><span class="brand-mark">P</span><span>Paw Pantry<small>Cat food &amp; care supplies</small></span></a>
    <h1>Buat akun pelanggan</h1>
    <p class="muted">Daftar untuk memesan dan melihat riwayat pembelian Anda.</p>
    @if($errors->any())<div class="alert error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('register.store') }}" class="stack-form">@csrf
        <label>Nama lengkap<input name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus></label>
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
        <label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
        <label>Ulangi password<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
        <button class="button primary full" type="submit">Daftar sebagai pelanggan <span>→</span></button>
    </form>
    <p class="login-hint">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
</main>
</body>
</html>

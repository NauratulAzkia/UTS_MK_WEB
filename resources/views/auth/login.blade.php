<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk · Paw Pantry</title>@vite(['resources/css/app.css', 'resources/css/inventory.css', 'resources/css/paw-pantry.css'])</head>
<body class="login-page">
<main class="login-card">
    <a class="brand login-brand" href="{{ route('login') }}"><span class="brand-mark">P</span><span>Paw Pantry<small>Cat food &amp; care supplies</small></span></a>
    <h1>Selamat datang kembali</h1><p class="muted">Masuk untuk berbelanja atau mengelola toko.</p>
    @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login.store') }}" class="stack-form">@csrf
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></label>
        <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
        <button class="button primary full" type="submit">Masuk <span>→</span></button>
    </form>
    <p class="login-hint">Belum punya akun pelanggan? <a href="{{ route('register') }}">Daftar di sini</a></p>
    <p class="login-hint">Akun demo staf: owner@example.test / password</p>
</main>
</body>
</html>

@extends('layouts.auth')
@section('title', 'Admin login')

@section('content')
<div class="auth-wrap">
    <aside class="auth-aside">
        <a href="{{ route('shop.index') }}" class="brand text-white" style="position:relative;z-index:1">
            <span class="brand-mark"><i class="bi bi-bag-heart-fill"></i></span> {{ config('app.name') }}
        </a>
        <div style="position:relative;z-index:1">
            <h2 class="mb-3">Manage your store<br>with confidence.</h2>
            <p class="mb-4">Catalogue, orders and payments in one place.</p>
            <ul class="list-unstyled mb-0">
                <li><i class="bi bi-diagram-3"></i> Categories, subcategories and products</li>
                <li><i class="bi bi-receipt"></i> Orders with status and payment tracking</li>
                <li><i class="bi bi-shield-lock"></i> Protected admin area</li>
            </ul>
        </div>
        <small style="position:relative;z-index:1" class="text-white-50">&copy; {{ date('Y') }} {{ config('app.name') }}</small>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            <h1 class="h3 mb-1">Admin login</h1>
            <p class="text-muted mb-4">Sign in to open the dashboard.</p>

            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autofocus required autocomplete="username"
                           class="form-control form-control-lg @error('email') is-invalid @enderror">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           class="form-control form-control-lg @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button class="btn btn-brand btn-lg w-100">Log in</button>
            </form>

            <a href="{{ route('shop.index') }}" class="d-inline-block mt-4 small"><i class="bi bi-arrow-left"></i> Back to the store</a>
        </div>
    </main>
</div>
@endsection

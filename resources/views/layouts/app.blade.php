<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Home') · {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', 'Shop quality products by category at '.config('app.name').'. Cash on delivery, easy order tracking.')">
    <link rel="canonical" href="{{ url()->current() }}">
    @stack('meta')
    @include('partials.assets')
</head>
<body>
<div class="topbar d-none d-md-block">
    <div class="container d-flex justify-content-between py-2">
        <span><i class="bi bi-truck"></i> Cash on delivery available all over Bangladesh</span>
        <span>
            <a href="{{ route('shop.track') }}"><i class="bi bi-geo-alt"></i> Track your order</a>
        </span>
    </div>
</div>

<header class="site-header">
    <div class="container py-3 d-flex align-items-center gap-3">
        <a href="{{ route('shop.index') }}" class="brand">
            <span class="brand-mark"><i class="bi bi-bag-heart-fill"></i></span>
            <span class="d-none d-sm-inline">{{ config('app.name') }}</span>
        </a>

        <form method="GET" action="{{ route('shop.index') }}" class="search-pill ms-lg-4" role="search">
            <input type="search" name="q" value="{{ request()->routeIs('shop.index') ? request('q') : '' }}"
                   class="form-control" placeholder="Search for products…" aria-label="Search products">
            <button class="btn btn-brand" aria-label="Search"><i class="bi bi-search"></i></button>
        </form>

        <nav class="ms-auto d-flex align-items-center gap-1">
            <a href="{{ route('shop.track') }}" class="header-link d-none d-md-inline-flex {{ request()->routeIs('shop.track*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Track order
            </a>
        </nav>
    </div>

    @if ($navCategories->isNotEmpty())
        <div class="cat-nav">
            <div class="container">
                <div class="scroller">
                    @php
                        $routeCategory = request()->route('category');
                        $activeNavSlug = $routeCategory instanceof \App\Models\Category ? $routeCategory->slug : (string) request('category');
                    @endphp
                    <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.index') && $activeNavSlug === '' ? 'active' : '' }}">All products</a>
                    @foreach ($navCategories as $navCategory)
                        <a href="{{ route('shop.category', $navCategory->slug) }}"
                           class="{{ $activeNavSlug === $navCategory->slug ? 'active' : '' }}">{{ $navCategory->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</header>

<main class="container py-4 py-lg-5">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="{{ route('shop.index') }}" class="brand mb-3">
                    <span class="brand-mark"><i class="bi bi-bag-heart-fill"></i></span> {{ config('app.name') }}
                </a>
                <p class="mt-3 mb-0">Quality products across electronics, fashion and home living, delivered to your door with cash on delivery and simple order tracking.</p>
            </div>
            <div class="col-6 col-lg-3 offset-lg-1">
                <h6>Shop</h6>
                <ul>
                    <li><a href="{{ route('shop.index') }}">All products</a></li>
                    @foreach ($navCategories->take(5) as $navCategory)
                        <li><a href="{{ route('shop.category', $navCategory->slug) }}">{{ $navCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="col-6 col-lg-4">
                <h6>Help</h6>
                <ul>
                    <li><a href="{{ route('shop.track') }}"><i class="bi bi-geo-alt"></i> Track your order</a></li>
                    <li><i class="bi bi-cash-coin"></i> Cash on delivery</li>
                    <li><i class="bi bi-phone"></i> bKash &amp; Nagad accepted</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container py-3 d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
            <span>Built with Laravel {{ app()->version() }}</span>
        </div>
    </div>
</footer>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') · {{ config('app.name') }} Admin</title>
    @include('partials.assets')
</head>
<body class="admin-body">
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

<div class="admin-shell">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            <span class="brand-mark"><i class="bi bi-bag-heart-fill"></i></span> {{ config('app.name') }}
        </a>

        <div class="nav-label">Overview</div>
        <a class="nav-item-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-label">Catalog</div>
        <a class="nav-item-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
            <i class="bi bi-diagram-3"></i> Categories
        </a>
        <a class="nav-item-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}" href="{{ route('admin.subcategories.index') }}">
            <i class="bi bi-diagram-2"></i> Subcategories
        </a>
        <a class="nav-item-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
            <i class="bi bi-box-seam"></i> Products
        </a>

        <div class="nav-label">Sales</div>
        <a class="nav-item-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
            <i class="bi bi-receipt"></i> Orders
            @if ($pendingOrdersCount > 0)<span class="count" title="Pending orders">{{ $pendingOrdersCount }}</span>@endif
        </a>

        <div class="nav-label">Store</div>
        <a class="nav-item-link" href="{{ route('shop.index') }}" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right"></i> View storefront
        </a>

        <div class="user-box">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <div class="text-white fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                    <div class="small text-truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="btn btn-sm btn-outline-light w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <button class="btn btn-icon d-lg-none" id="sidebar-toggle" aria-label="Open menu"><i class="bi bi-list fs-5"></i></button>
            <nav aria-label="breadcrumb" class="flex-grow-1">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@yield('title', 'Admin')</li>
                </ol>
            </nav>
            <a href="{{ route('shop.index') }}" class="btn btn-sm btn-outline-ink d-none d-sm-inline-flex" target="_blank" rel="noopener">
                <i class="bi bi-shop"></i>&nbsp;Storefront
            </a>
        </div>

        <main class="admin-content">
            @include('partials.flash')

            <div class="page-head">
                <div>
                    <h1>@yield('title', 'Admin')</h1>
                    @hasSection('subtitle')<p class="sub">@yield('subtitle')</p>@endif
                </div>
                <div class="d-flex flex-wrap gap-2">@yield('actions')</div>
            </div>

            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
    (function () {
        var sidebar = document.getElementById('sidebar');
        var backdrop = document.getElementById('sidebar-backdrop');
        var toggle = document.getElementById('sidebar-toggle');
        function set(open) { sidebar.classList.toggle('open', open); backdrop.classList.toggle('show', open); }
        if (toggle) { toggle.addEventListener('click', function () { set(true); }); }
        backdrop.addEventListener('click', function () { set(false); });
    })();
</script>
@stack('scripts')
</body>
</html>

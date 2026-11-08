@extends('layouts.app')
@section('title', $search !== '' ? 'Search: '.$search : 'All products')

@section('content')
    @php $showHero = $search === '' && $activeCategory === '' && $sections->currentPage() === 1; @endphp

    @if ($showHero)
        <section class="hero mb-4 mb-lg-5">
            <div class="position-relative" style="z-index:1">
                <span class="badge text-bg-light text-brand mb-3 px-3 py-2"><i class="bi bi-stars"></i> New arrivals every week</span>
                <h1 class="text-balance mb-3">Everything you need,<br>delivered to your door.</h1>
                <p class="mb-4">Browse electronics, fashion and home essentials. Order in a minute with cash on delivery, then track it with your personal token.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#products" class="btn btn-light btn-lg"><i class="bi bi-bag"></i> Shop now</a>
                    <a href="{{ route('shop.track') }}" class="btn btn-outline-light btn-lg"><i class="bi bi-geo-alt"></i> Track order</a>
                </div>
                <div class="hero-stats">
                    <div><strong>{{ $stats['products'] }}+</strong><span>Products</span></div>
                    <div><strong>{{ $stats['categories'] }}</strong><span>Categories</span></div>
                    <div><strong>COD</strong><span>Pay on delivery</span></div>
                </div>
            </div>
        </section>

        <section class="feature-strip mb-5">
            <div class="feature"><span class="ico"><i class="bi bi-truck"></i></span><div><strong>Fast delivery</strong><span>All over Bangladesh</span></div></div>
            <div class="feature"><span class="ico"><i class="bi bi-cash-coin"></i></span><div><strong>Cash on delivery</strong><span>Pay when you receive</span></div></div>
            <div class="feature"><span class="ico"><i class="bi bi-geo-alt"></i></span><div><strong>Order tracking</strong><span>Live status with your token</span></div></div>
            <div class="feature"><span class="ico"><i class="bi bi-shield-check"></i></span><div><strong>Secure ordering</strong><span>Your details stay private</span></div></div>
        </section>
    @endif

    <div id="products" class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">
                @if ($search !== '') Results for “{{ $search }}” @elseif ($currentCategory) {{ $currentCategory->name }} @else All products @endif
            </h1>
            <p class="text-muted mb-0">{{ $sections->total() }} {{ \Illuminate\Support\Str::plural('section', $sections->total()) }} · organised by subcategory</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('shop.index', array_filter(['q' => $search])) }}" class="chip {{ $activeCategory === '' ? 'active' : '' }}">All</a>
            @foreach ($categories as $category)
                <a href="{{ route('shop.category', array_filter(['category' => $category->slug, 'q' => $search])) }}"
                   class="chip {{ $activeCategory === $category->slug ? 'active' : '' }}">{{ $category->name }}</a>
            @endforeach
        </div>
    </div>

    @forelse ($sections as $subcategory)
        <section class="mb-5" id="subcategory-{{ $subcategory->slug }}">
            <div class="section-head">
                <div>
                    <div class="eyebrow">{{ $subcategory->category->name }}</div>
                    <h2>{{ $subcategory->name }}</h2>
                </div>
                <a href="{{ route('shop.subcategory', $subcategory->slug) }}" class="btn btn-sm btn-outline-ink">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                @foreach ($subcategory->products as $product)
                    <div class="col">@include('partials.product-card', ['product' => $product, 'meta' => $subcategory->name])</div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="card"><div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p class="mb-3">No products found.</p>
            <a href="{{ route('shop.index') }}" class="btn btn-brand btn-sm">Clear filters</a>
        </div></div>
    @endforelse

    <div class="d-flex justify-content-center">{{ $sections->links() }}</div>
@endsection

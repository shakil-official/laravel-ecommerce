@extends('layouts.app')
@section('title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description), 155))

@push('meta')
    <meta property="og:title" content="{{ $product->name }}">
    <meta property="og:type" content="product">
    <meta property="og:image" content="{{ $product->image_url }}">
    <meta property="og:url" content="{{ route('shop.product', $product->slug) }}">
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => $product->image_url,
        'description' => $product->description,
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'BDT',
            'price' => (string) $product->new_price,
            'availability' => 'https://schema.org/InStock',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}">Shop</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.subcategory', $product->subcategory->slug) }}">{{ $product->subcategory->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="gallery">
                @if ($product->has_discount)
                    <span class="badge-discount fs-6">-{{ $product->discount_percent }}%</span>
                @endif
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
            </div>
            <div class="card mt-4"><div class="card-body p-4">
                <h2 class="h5 mb-3">Description</h2>
                <p class="mb-0 pre-line">{{ $product->description }}</p>
            </div></div>
        </div>

        <div class="col-lg-6">
            <div class="order-panel">
                <div class="small text-muted text-uppercase fw-semibold mb-1">{{ $product->category->name }} · {{ $product->subcategory->name }}</div>
                <h1 class="h2 mb-3">{{ $product->name }}</h1>

                <div class="price-box mb-3">
                    <span class="now">{{ \App\Support\Money::format($product->new_price) }}</span>
                    @if ($product->has_discount)
                        <span class="was">{{ \App\Support\Money::format($product->old_price) }}</span>
                        <div class="save mt-1"><i class="bi bi-piggy-bank"></i> You save {{ \App\Support\Money::format($product->old_price - $product->new_price) }}</div>
                    @endif
                </div>

                <ul class="trust-list mb-4">
                    <li><i class="bi bi-cash-coin"></i> Cash on delivery available</li>
                    <li><i class="bi bi-truck"></i> Delivery all over Bangladesh</li>
                    <li><i class="bi bi-geo-alt"></i> Track your order with a personal token</li>
                </ul>

                <div class="card" id="order-form">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3"><i class="bi bi-bag-check text-brand"></i> Order now</h2>
                        <form method="POST" action="{{ route('shop.order.store', $product->slug) }}" novalidate
                              data-unit-price="{{ $product->new_price }}" data-currency="{{ config('shop.currency') }}" id="order-form-el">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="customer_name" class="form-label">Your name <span class="text-danger">*</span></label>
                                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" maxlength="100"
                                           class="form-control @error('customer_name') is-invalid @enderror" required>
                                    @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Mobile number <span class="text-danger">*</span></label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX"
                                           class="form-control @error('phone') is-invalid @enderror" required>
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="150"
                                           class="form-control @error('email') is-invalid @enderror">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6 col-md-3">
                                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="100"
                                           class="form-control @error('quantity') is-invalid @enderror" required>
                                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6 col-md-3">
                                    <label for="payment_method" class="form-label">Payment <span class="text-danger">*</span></label>
                                    <select id="payment_method" name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                        @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                            <option value="{{ $method->value }}" @selected(old('payment_method', 'cod') === $method->value)>{{ $method->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="address" class="form-label">Delivery address <span class="text-danger">*</span></label>
                                    <textarea id="address" name="address" rows="2" maxlength="500"
                                              class="form-control @error('address') is-invalid @enderror" required>{{ old('address') }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="note" class="form-label">Note <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="text" id="note" name="note" value="{{ old('note') }}" maxlength="500" class="form-control @error('note') is-invalid @enderror">
                                    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="order-total d-flex justify-content-between align-items-center mt-4">
                                <span class="text-muted">Order total</span>
                                <strong class="fs-5" id="order-total">{{ \App\Support\Money::format($product->new_price) }}</strong>
                            </div>
                            <button class="btn btn-brand btn-lg w-100 mt-3"><i class="bi bi-bag-check"></i> Place order</button>
                            <p class="small text-muted text-center mt-2 mb-0">You will get a tracking token right after ordering.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="related-section" aria-labelledby="related-title">
            <div class="section-head">
                <div>
                    <div class="eyebrow">More like this</div>
                    <h2 id="related-title">Related products</h2>
                    <p class="text-muted small mb-0 mt-1">More from {{ $product->subcategory->name }}</p>
                </div>
                <a href="{{ route('shop.subcategory', $product->subcategory->slug) }}" class="btn btn-sm btn-outline-ink">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-md-4">
                @foreach ($related as $item)
                    <div class="col">@include('partials.product-card', ['product' => $item])</div>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        // Live order total (the server always recalculates it from the database price).
        var form = document.getElementById('order-form-el');
        var qty = document.getElementById('quantity');
        var out = document.getElementById('order-total');
        if (!form || !qty || !out) { return; }
        var unit = parseFloat(form.dataset.unitPrice) || 0;
        function update() {
            var q = Math.max(1, Math.min(100, parseInt(qty.value, 10) || 1));
            out.textContent = form.dataset.currency + ' ' + (unit * q).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        qty.addEventListener('input', update);
        update();
    })();
</script>
@endpush

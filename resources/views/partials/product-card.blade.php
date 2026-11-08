{{-- Props: $product, optional $meta (small label above the title) --}}
<article class="card product-card">
    @if ($product->has_discount)
        <span class="badge-discount">-{{ $product->discount_percent }}%</span>
    @endif
    <a href="{{ route('shop.product', $product->slug) }}" class="thumb d-block" tabindex="-1" aria-hidden="true">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
    </a>
    <div class="card-body d-flex flex-column p-3">
        @isset($meta)<div class="meta mb-1">{{ $meta }}</div>@endisset
        <h3 class="title mb-2"><a href="{{ route('shop.product', $product->slug) }}" class="text-reset">{{ $product->name }}</a></h3>
        <div class="mt-auto">
            <div class="d-flex align-items-baseline flex-wrap column-gap-2">
                <span class="price-new">{{ \App\Support\Money::format($product->new_price) }}</span>
                @if ($product->has_discount)
                    <span class="price-old ms-0">{{ \App\Support\Money::format($product->old_price) }}</span>
                @endif
            </div>
            @if ($product->has_discount)
                <div class="price-save">Save {{ \App\Support\Money::format($product->old_price - $product->new_price) }}</div>
            @endif
            <a href="{{ route('shop.product', $product->slug) }}#order-form" class="btn btn-sm btn-soft order-btn w-100 mt-3"><i class="bi bi-bag-plus me-1"></i>Order</a>
        </div>
    </div>
</article>

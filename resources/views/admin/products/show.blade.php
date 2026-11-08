@extends('layouts.admin')
@section('title', $product->name)
@section('subtitle', 'Product details')
@section('actions')
    <a href="{{ route('shop.product', $product->slug) }}" class="btn btn-outline-ink" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> View on shop</a>
    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-brand"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-ink">Back</a>
@endsection

@section('content')
    <div class="card"><div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-4"><img src="{{ $product->image_url }}" class="img-fluid rounded-3 border" alt="{{ $product->name }}"></div>
            <div class="col-md-8">
                <dl class="row detail-list mb-0">
                    <dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $product->category->name }}</dd>
                    <dt class="col-sm-3">Subcategory</dt><dd class="col-sm-9">{{ $product->subcategory->name }}</dd>
                    <dt class="col-sm-3">Slug</dt><dd class="col-sm-9"><code class="code-chip">{{ $product->slug }}</code></dd>
                    <dt class="col-sm-3">Old price</dt><dd class="col-sm-9">{{ \App\Support\Money::format($product->old_price) }}</dd>
                    <dt class="col-sm-3">New price</dt>
                    <dd class="col-sm-9 fw-bold text-brand">{{ \App\Support\Money::format($product->new_price) }}
                        @if ($product->has_discount)<span class="pill pill-danger ms-1">-{{ $product->discount_percent }}%</span>@endif
                    </dd>
                    <dt class="col-sm-3">Description</dt><dd class="col-sm-9 pre-line">{{ $product->description }}</dd>
                    <dt class="col-sm-3">Created</dt><dd class="col-sm-9 mb-0">{{ $product->created_at->format('d M Y, h:i A') }}</dd>
                </dl>
            </div>
        </div>
    </div></div>
@endsection

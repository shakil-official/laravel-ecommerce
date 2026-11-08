@extends('layouts.app')
@section('title', $subcategory->name)
@section('meta_description', $subcategory->description ?: 'Shop '.$subcategory->name.' in '.$subcategory->category->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}">Shop</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.category', $subcategory->category->slug) }}">{{ $subcategory->category->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $subcategory->name }}</li>
        </ol>
    </nav>

    <div class="card mb-4"><div class="card-body p-4">
        <div class="eyebrow text-brand fw-bold small text-uppercase">{{ $subcategory->category->name }}</div>
        <h1 class="h3 mb-1">{{ $subcategory->name }}</h1>
        @if ($subcategory->description)<p class="text-muted mb-0">{{ $subcategory->description }}</p>@endif
    </div></div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
        @forelse ($products as $product)
            <div class="col">@include('partials.product-card', ['product' => $product])</div>
        @empty
            <div class="col-12"><div class="card"><div class="empty-state"><i class="bi bi-inbox"></i>No products in this subcategory yet.</div></div></div>
        @endforelse
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $products->links() }}</div>
@endsection

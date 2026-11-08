@extends('layouts.admin')
@section('title', $subcategory->name)
@section('subtitle', 'Subcategory details')
@section('actions')
    <a href="{{ route('admin.products.create', ['category_id' => $subcategory->category_id, 'subcategory_id' => $subcategory->id]) }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Add product</a>
    <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="btn btn-outline-ink"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline-ink">Back</a>
@endsection

@section('content')
    <div class="card mb-4"><div class="card-body p-4">
        <dl class="row detail-list mb-0">
            <dt class="col-sm-3">Category</dt><dd class="col-sm-9"><a href="{{ route('admin.categories.show', $subcategory->category) }}">{{ $subcategory->category->name }}</a></dd>
            <dt class="col-sm-3">Slug</dt><dd class="col-sm-9"><code class="code-chip">{{ $subcategory->slug }}</code></dd>
            <dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $subcategory->description ?: '—' }}</dd>
            <dt class="col-sm-3">Created</dt><dd class="col-sm-9 mb-0">{{ $subcategory->created_at->format('d M Y, h:i A') }}</dd>
        </dl>
    </div></div>

    <div class="card table-card">
        <div class="card-header">Products</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th></th><th>Name</th><th class="text-end">Old price</th><th class="text-end">New price</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td style="width:76px"><img src="{{ $product->image_url }}" class="admin-thumb" alt=""></td>
                        <td class="fw-semibold">{{ $product->name }}</td>
                        <td class="text-end text-muted">{{ \App\Support\Money::format($product->old_price) }}</td>
                        <td class="text-end fw-semibold">{{ \App\Support\Money::format($product->new_price) }}</td>
                        <td class="text-end"><a href="{{ route('admin.products.show', $product) }}" class="btn-icon" title="View"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state"><i class="bi bi-box-seam"></i>No products yet.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.table-footer', ['paginator' => $products])
    </div>
@endsection

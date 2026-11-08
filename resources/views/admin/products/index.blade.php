@extends('layouts.admin')
@section('title', 'Products')
@section('subtitle', 'Everything you sell')
@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New product</a>
@endsection

@section('content')
    <div class="card table-card">
        <form method="GET" class="filters row g-2 align-items-center">
            <div class="col-sm-5 col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-5 col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search by name…"></div>
            <div class="col-auto"><button class="btn btn-sm btn-soft"><i class="bi bi-funnel"></i> Filter</button></div>
            @if (request('q') || request('category_id') || request('subcategory_id'))<div class="col-auto"><a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-ink">Reset</a></div>@endif
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th></th><th>Product</th><th>Category</th><th class="text-end">Old</th><th class="text-end">New</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td style="width:76px"><img src="{{ $product->image_url }}" class="admin-thumb" alt=""></td>
                        <td>
                            <a href="{{ route('admin.products.show', $product) }}" class="fw-semibold">{{ $product->name }}</a>
                            <div><code class="code-chip">{{ $product->slug }}</code></div>
                        </td>
                        <td>{{ $product->category->name }} <i class="bi bi-chevron-right small text-muted"></i> {{ $product->subcategory->name }}</td>
                        <td class="text-end text-muted">{{ \App\Support\Money::format($product->old_price) }}</td>
                        <td class="text-end fw-semibold">{{ \App\Support\Money::format($product->new_price) }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.products.show', $product) }}" class="btn-icon" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline"
                                  onsubmit="return confirm(@js('Delete “'.$product->name.'”?'))">
                                @csrf @method('DELETE')
                                <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-box-seam"></i>No products found.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.table-footer', ['paginator' => $products])
    </div>
@endsection

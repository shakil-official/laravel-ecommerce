@extends('layouts.admin')
@section('title', $category->name)
@section('subtitle', 'Category details')
@section('actions')
    <a href="{{ route('admin.subcategories.create', ['category_id' => $category->id]) }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Add subcategory</a>
    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-outline-ink"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-ink">Back</a>
@endsection

@section('content')
    <div class="card mb-4"><div class="card-body p-4">
        <dl class="row detail-list mb-0">
            <dt class="col-sm-3">Slug</dt><dd class="col-sm-9"><code class="code-chip">{{ $category->slug }}</code></dd>
            <dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $category->description ?: '—' }}</dd>
            <dt class="col-sm-3">Total products</dt><dd class="col-sm-9">{{ $category->products_count }}</dd>
            <dt class="col-sm-3">Created</dt><dd class="col-sm-9 mb-0">{{ $category->created_at->format('d M Y, h:i A') }}</dd>
        </dl>
    </div></div>

    <div class="card table-card">
        <div class="card-header">Subcategories</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Name</th><th>Slug</th><th class="text-center">Products</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($subcategories as $subcategory)
                    <tr>
                        <td class="fw-semibold">{{ $subcategory->name }}</td>
                        <td><code class="code-chip">{{ $subcategory->slug }}</code></td>
                        <td class="text-center">{{ $subcategory->products_count }}</td>
                        <td class="text-end"><a href="{{ route('admin.subcategories.show', $subcategory) }}" class="btn-icon" title="View"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state"><i class="bi bi-diagram-2"></i>No subcategories yet.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.table-footer', ['paginator' => $subcategories])
    </div>
@endsection

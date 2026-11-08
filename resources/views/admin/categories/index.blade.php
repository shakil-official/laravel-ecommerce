@extends('layouts.admin')
@section('title', 'Categories')
@section('subtitle', 'Top-level groups of your catalogue')
@section('actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New category</a>
@endsection

@section('content')
    <div class="card table-card">
        <form method="GET" class="filters row g-2 align-items-center">
            <div class="col-sm-6 col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search by name…"></div>
            <div class="col-auto"><button class="btn btn-sm btn-soft"><i class="bi bi-search"></i> Search</button></div>
            @if (request('q'))<div class="col-auto"><a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-ink">Reset</a></div>@endif
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>#</th><th>Name</th><th>Slug</th><th class="text-center">Subcategories</th><th class="text-center">Products</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="text-muted">{{ $category->id }}</td>
                        <td><a href="{{ route('admin.categories.show', $category) }}" class="fw-semibold">{{ $category->name }}</a></td>
                        <td><code class="code-chip">{{ $category->slug }}</code></td>
                        <td class="text-center">{{ $category->subcategories_count }}</td>
                        <td class="text-center">{{ $category->products_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.categories.show', $category) }}" class="btn-icon" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline"
                                  onsubmit="return confirm(@js('Delete “'.$category->name.'”? Its '.$category->subcategories_count.' subcategories and '.$category->products_count.' products will be deleted too.'))">
                                @csrf @method('DELETE')
                                <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-diagram-3"></i>No categories found.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.table-footer', ['paginator' => $categories])
    </div>
@endsection

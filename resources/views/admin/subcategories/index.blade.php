@extends('layouts.admin')
@section('title', 'Subcategories')
@section('subtitle', 'Groups inside a category')
@section('actions')
    <a href="{{ route('admin.subcategories.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New subcategory</a>
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
            @if (request('q') || request('category_id'))<div class="col-auto"><a href="{{ route('admin.subcategories.index') }}" class="btn btn-sm btn-outline-ink">Reset</a></div>@endif
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>#</th><th>Name</th><th>Category</th><th>Slug</th><th class="text-center">Products</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($subcategories as $subcategory)
                    <tr>
                        <td class="text-muted">{{ $subcategory->id }}</td>
                        <td><a href="{{ route('admin.subcategories.show', $subcategory) }}" class="fw-semibold">{{ $subcategory->name }}</a></td>
                        <td>{{ $subcategory->category->name }}</td>
                        <td><code class="code-chip">{{ $subcategory->slug }}</code></td>
                        <td class="text-center">{{ $subcategory->products_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.subcategories.show', $subcategory) }}" class="btn-icon" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.subcategories.destroy', $subcategory) }}" class="d-inline"
                                  onsubmit="return confirm(@js('Delete “'.$subcategory->name.'”? Its '.$subcategory->products_count.' products will be deleted too.'))">
                                @csrf @method('DELETE')
                                <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-diagram-2"></i>No subcategories found.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.table-footer', ['paginator' => $subcategories])
    </div>
@endsection

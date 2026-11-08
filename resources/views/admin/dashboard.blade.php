@extends('layouts.admin')
@section('title', 'Dashboard')
@section('subtitle', 'Overview of your catalogue and orders')
@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Add product</a>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        @foreach ([
            ['Orders', $orderStats['orders'], 'bi-receipt', 'ico-indigo', route('admin.orders.index')],
            ['Pending orders', $orderStats['pending'], 'bi-hourglass-split', 'ico-amber', route('admin.orders.index', ['status' => 'pending'])],
            ['Paid revenue', \App\Support\Money::format($orderStats['revenue']), 'bi-cash-coin', 'ico-green', route('admin.orders.index', ['payment_status' => 'paid'])],
            ['Products', $stats['products'], 'bi-box-seam', 'ico-sky', route('admin.products.index')],
            ['Subcategories', $stats['subcategories'], 'bi-diagram-2', 'ico-violet', route('admin.subcategories.index')],
            ['Categories', $stats['categories'], 'bi-diagram-3', 'ico-rose', route('admin.categories.index')],
        ] as [$label, $value, $icon, $tone, $url])
            <div class="col-sm-6 col-xl-4">
                <a href="{{ $url }}" class="card stat-card text-decoration-none">
                    <span class="ico {{ $tone }}"><i class="bi {{ $icon }}"></i></span>
                    <div>
                        <div class="label">{{ $label }}</div>
                        <div class="value">{{ $value }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card table-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Latest orders</span>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-ink">All orders</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <tbody>
                @forelse ($latestOrders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->order_number }}</a>
                            <div class="small text-muted">{{ $order->customer_name }}</div>
                        </td>
                        <td>{{ $order->product_name }} &times; {{ $order->quantity }}</td>
                        <td>
                            <span class="pill pill-{{ $order->status->color() }}">{{ $order->status->label() }}</span>
                            <span class="pill pill-{{ $order->payment_status->color() }}">{{ $order->payment_status->label() }}</span>
                        </td>
                        <td class="text-end fw-semibold">{{ \App\Support\Money::format($order->total) }}</td>
                    </tr>
                @empty
                    <tr><td><div class="empty-state py-4"><i class="bi bi-receipt"></i>No orders yet.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card table-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Latest products</span>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-ink">All products</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <tbody>
                        @forelse ($latestProducts as $product)
                            <tr>
                                <td style="width:76px"><img src="{{ $product->image_url }}" class="admin-thumb" alt=""></td>
                                <td>
                                    <a href="{{ route('admin.products.show', $product) }}" class="fw-semibold">{{ $product->name }}</a>
                                    <div class="small text-muted">{{ $product->category->name }} / {{ $product->subcategory->name }}</div>
                                </td>
                                <td class="text-end fw-semibold">{{ \App\Support\Money::format($product->new_price) }}</td>
                            </tr>
                        @empty
                            <tr><td><div class="empty-state py-4"><i class="bi bi-box-seam"></i>No products yet.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card table-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Categories</span>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-ink">Manage</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($categories as $category)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <a href="{{ route('admin.categories.show', $category) }}" class="fw-semibold">{{ $category->name }}</a>
                            <span class="small text-muted">{{ $category->subcategories_count }} sub · {{ $category->products_count }} products</span>
                        </li>
                    @empty
                        <li class="list-group-item"><div class="empty-state py-4"><i class="bi bi-diagram-3"></i>No categories yet.</div></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.admin')
@section('title', 'Order '.$order->order_number)
@section('subtitle', 'Placed '.$order->created_at->format('d M Y, h:i A'))
@section('actions')
    <a href="{{ route('shop.track.show', $order->tracking_token) }}" class="btn btn-outline-ink" target="_blank" rel="noopener"><i class="bi bi-geo-alt"></i> Customer tracking page</a>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-ink">Back</a>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Update status</h2>
                @include('admin.orders._status-form')
                <div class="mt-3 d-flex gap-2">
                    <span class="pill pill-{{ $order->status->color() }}">{{ $order->status->label() }}</span>
                    <span class="pill pill-{{ $order->payment_status->color() }}">Payment: {{ $order->payment_status->label() }}</span>
                </div>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Tracking token</h2>
                <div class="token-box mb-2"><code>{{ $order->tracking_token }}</code></div>
                <p class="small text-muted mb-0">The customer enters this token on the <a href="{{ route('shop.track') }}" target="_blank" rel="noopener">tracking page</a> to see the order status.</p>
            </div></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-body p-4">
                <h2 class="h5 mb-3">Order</h2>
                <dl class="row detail-list mb-0">
                    <dt class="col-sm-4">Product</dt>
                    <dd class="col-sm-8">
                        @if ($order->product)
                            <a href="{{ route('admin.products.show', $order->product) }}">{{ $order->product_name }}</a>
                        @else
                            {{ $order->product_name }} <span class="pill pill-secondary">product deleted</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Unit price</dt><dd class="col-sm-8">{{ \App\Support\Money::format($order->unit_price) }}</dd>
                    <dt class="col-sm-4">Quantity</dt><dd class="col-sm-8">{{ $order->quantity }}</dd>
                    <dt class="col-sm-4">Total</dt><dd class="col-sm-8 fw-bold text-brand">{{ \App\Support\Money::format($order->total) }}</dd>
                    <dt class="col-sm-4">Payment method</dt><dd class="col-sm-8 mb-0">{{ $order->payment_method->label() }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-body p-4">
                <h2 class="h5 mb-3">Customer</h2>
                <dl class="row detail-list mb-0">
                    <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $order->customer_name }}</dd>
                    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8"><a href="tel:{{ $order->phone }}">{{ $order->phone }}</a></dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $order->email ?: '—' }}</dd>
                    <dt class="col-sm-4">Address</dt><dd class="col-sm-8 pre-line">{{ $order->address }}</dd>
                    <dt class="col-sm-4">Note</dt><dd class="col-sm-8 mb-0">{{ $order->note ?: '—' }}</dd>
                </dl>
            </div></div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="mt-4"
          onsubmit="return confirm(@js('Delete order '.$order->order_number.'?'))">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete order</button>
    </form>
@endsection

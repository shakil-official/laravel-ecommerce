@extends('layouts.admin')
@section('title', 'Orders')
@section('subtitle', 'Search orders and update order and payment status')

@section('content')
    <div class="card table-card">
        <form method="GET" class="filters row g-2 align-items-center">
            <div class="col-sm-6 col-md-3"><input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Order no, token, name or phone…"></div>
            <div class="col-sm-3 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3 col-md-2">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">All payments</option>
                    @foreach ($paymentStatuses as $paymentStatus)
                        <option value="{{ $paymentStatus->value }}" @selected(request('payment_status') === $paymentStatus->value)>{{ $paymentStatus->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-soft"><i class="bi bi-funnel"></i> Filter</button></div>
            @if (request('q') || request('status') || request('payment_status'))<div class="col-auto"><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-ink">Reset</a></div>@endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Order</th><th>Customer</th><th>Product</th><th class="text-end">Total</th><th>Update status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold order-no">{{ $order->order_number }}</a>
                            <div class="small text-muted">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                            <div class="mt-1 text-nowrap"><span class="pill pill-{{ $order->status->color() }}">{{ $order->status->label() }}</span> <span class="pill pill-{{ $order->payment_status->color() }}">{{ $order->payment_status->label() }}</span></div>
                        </td>
                        <td>{{ $order->customer_name }}<div class="small text-muted">{{ $order->phone }}</div></td>
                        <td>{{ $order->product_name }} &times; {{ $order->quantity }}<div class="small text-muted">{{ $order->payment_method->label() }}</div></td>
                        <td class="text-end fw-semibold">{{ \App\Support\Money::format($order->total) }}</td>
<td>@include('admin.orders._status-form')</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn-icon" title="View"><i class="bi bi-eye"></i></a>
                            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="d-inline"
                                  onsubmit="return confirm(@js('Delete order '.$order->order_number.'?'))">
                                @csrf @method('DELETE')
                                <button class="btn-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-receipt"></i>No orders found.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.table-footer', ['paginator' => $orders])
    </div>
@endsection

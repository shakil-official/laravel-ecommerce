@extends('layouts.app')
@section('title', $order ? 'Order '.$order->order_number : 'Track your order')
@section('meta_description', 'Track your order status with your personal tracking token.')

@section('content')
    <div class="track-hero mb-4 mb-lg-5">
        <div class="row justify-content-center text-center">
            <div class="col-lg-7">
                <h1 class="mb-2"><i class="bi bi-geo-alt-fill"></i> Track your order</h1>
                <p class="mb-4 text-white-50">Enter the tracking token you received after placing your order.</p>
                <form method="POST" action="{{ route('shop.track.lookup') }}" novalidate>
                    @csrf
                    <div class="input-group input-group-lg has-validation">
                        <input type="text" name="token" id="token" value="{{ old('token', $order?->tracking_token) }}"
                               class="form-control token-input @error('token') is-invalid @enderror"
                               placeholder="XXXX-XXXX-XXXX" maxlength="20" autocomplete="off" autocapitalize="characters" required>
                        <button class="btn btn-warning fw-bold px-4"><i class="bi bi-search"></i> Track</button>
                        @error('token')<div class="invalid-feedback text-start bg-white rounded-bottom px-3 py-2">{{ $message }}</div>@enderror
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($order)
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4"><div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold">Order number</div>
                            <h2 class="h4 mb-0">{{ $order->order_number }}</h2>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="pill pill-{{ $order->status->color() }}">{{ $order->status->label() }}</span>
                            <span class="pill pill-{{ $order->payment_status->color() }}">Payment: {{ $order->payment_status->label() }}</span>
                        </div>
                    </div>

                    @if ($order->status === \App\Enums\OrderStatus::Cancelled)
                        <div class="timeline-cancelled">
                            <i class="bi bi-x-octagon-fill"></i>
                            <div><strong>Order cancelled</strong><div class="small">{{ $order->status->description() }} Contact us if this is a mistake.</div></div>
                        </div>
                    @else
                        @php
                            $current = $order->status->step();
                            $delivered = $order->status === \App\Enums\OrderStatus::Delivered;
                        @endphp
                        <ol class="timeline">
                            @foreach (\App\Enums\OrderStatus::trackingSteps() as $i => $step)
                                @php $state = ($i < $current || $delivered) ? 'done' : ($i === $current ? 'current' : ''); @endphp
                                <li class="{{ $state }}">
                                    <span class="dot"><i class="bi {{ $state === 'done' ? 'bi-check-lg' : $step->icon() }}"></i></span>
                                    <div class="label">{{ $step->label() }}</div>
                                </li>
                            @endforeach
                        </ol>
                        <div class="bg-brand-soft rounded-3 p-3 mt-4 text-center">
                            <i class="bi {{ $order->status->icon() }} text-brand"></i>
                            <strong>{{ $order->status->label() }}:</strong> {{ $order->status->description() }}
                        </div>
                    @endif
                </div></div>

                <div class="card"><div class="card-body p-4">
                    <h2 class="h5 mb-3">Order details</h2>
                    <dl class="row detail-list mb-0">
                        <dt class="col-sm-4">Product</dt><dd class="col-sm-8">{{ $order->product_name }} &times; {{ $order->quantity }}</dd>
                        <dt class="col-sm-4">Unit price</dt><dd class="col-sm-8">{{ \App\Support\Money::format($order->unit_price) }}</dd>
                        <dt class="col-sm-4">Total</dt><dd class="col-sm-8 fw-bold text-brand">{{ \App\Support\Money::format($order->total) }}</dd>
                        <dt class="col-sm-4">Payment method</dt><dd class="col-sm-8">{{ $order->payment_method->label() }}</dd>
                        <dt class="col-sm-4">Placed on</dt><dd class="col-sm-8">{{ $order->created_at->format('d M Y, h:i A') }}</dd>
                        <dt class="col-sm-4">Last update</dt><dd class="col-sm-8">{{ $order->updated_at->diffForHumans() }}</dd>
                    </dl>
                </div></div>
            </div>

            <div class="col-lg-4">
                <div class="card"><div class="card-body p-4">
                    <h2 class="h5 mb-3">Delivery</h2>
                    <dl class="detail-list mb-0">
                        <dt>Name</dt><dd>{{ $order->customer_name }}</dd>
                        <dt>Phone</dt><dd>{{ $order->masked_phone }}</dd>
                        <dt>Address</dt><dd class="mb-0 pre-line">{{ $order->address }}</dd>
                    </dl>
                </div></div>
                <a href="{{ route('shop.index') }}" class="btn btn-outline-ink w-100 mt-3"><i class="bi bi-bag"></i> Continue shopping</a>
            </div>
        </div>
    @else
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="feature-strip">
                    <div class="feature"><span class="ico"><i class="bi bi-1-circle"></i></span><div><strong>Order a product</strong><span>Use the Order now form</span></div></div>
                    <div class="feature"><span class="ico"><i class="bi bi-2-circle"></i></span><div><strong>Get your token</strong><span>Shown right after ordering</span></div></div>
                    <div class="feature"><span class="ico"><i class="bi bi-3-circle"></i></span><div><strong>Track it here</strong><span>See live status updates</span></div></div>
                </div>
            </div>
        </div>
    @endif
@endsection

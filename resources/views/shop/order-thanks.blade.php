@extends('layouts.app')
@section('title', 'Order placed')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card"><div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="feature mx-auto justify-content-center border-0 bg-transparent p-0 mb-3">
                        <span class="ico" style="width:72px;height:72px;font-size:2rem;background:#dcfce7;color:#16a34a;border-radius:50%"><i class="bi bi-check-lg"></i></span>
                    </div>
                    <h1 class="h3 mb-2">Thank you, {{ $order->customer_name }}!</h1>
                    <p class="text-muted mb-0">Your order has been received. We will contact you on <strong>{{ $order->phone }}</strong> to confirm it.</p>
                </div>

                <div class="bg-brand-soft rounded-4 p-4 text-center mb-4">
                    <div class="small text-uppercase fw-bold text-brand mb-2">Your tracking token</div>
                    <div class="token-box">
                        <code id="token">{{ $order->tracking_token }}</code>
                        <button type="button" class="btn btn-sm btn-brand copy-btn" data-copy="{{ $order->tracking_token }}"><i class="bi bi-clipboard"></i> Copy</button>
                    </div>
                    <p class="small text-muted mt-3 mb-3">Save this token. You need it to track your order. Keep it private.</p>
                    <a href="{{ route('shop.track.show', $order->tracking_token) }}" class="btn btn-brand"><i class="bi bi-geo-alt"></i> Track this order</a>
                </div>

                <dl class="row detail-list mb-0">
                    <dt class="col-sm-4">Order number</dt><dd class="col-sm-8 fw-bold">{{ $order->order_number }}</dd>
                    <dt class="col-sm-4">Product</dt><dd class="col-sm-8">{{ $order->product_name }} &times; {{ $order->quantity }}</dd>
                    <dt class="col-sm-4">Total</dt><dd class="col-sm-8 fw-bold text-brand">{{ \App\Support\Money::format($order->total) }}</dd>
                    <dt class="col-sm-4">Payment</dt><dd class="col-sm-8">{{ $order->payment_method->label() }}</dd>
                    <dt class="col-sm-4">Delivery address</dt><dd class="col-sm-8 pre-line">{{ $order->address }}</dd>
                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="pill pill-{{ $order->status->color() }}">{{ $order->status->label() }}</span></dd>
                </dl>

                <div class="text-center mt-4">
                    <a href="{{ route('shop.index') }}" class="btn btn-outline-ink">Continue shopping</a>
                </div>
            </div></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = btn.dataset.copy;
            var done = function () {
                btn.classList.add('copied'); btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
                setTimeout(function () { btn.classList.remove('copied'); btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
            };
            if (navigator.clipboard) { navigator.clipboard.writeText(text).then(done); }
            else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); done(); }
        });
    });
</script>
@endpush

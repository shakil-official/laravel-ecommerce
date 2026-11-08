<form method="POST" action="{{ route('admin.orders.update', $order) }}" class="status-form d-flex flex-wrap gap-2 align-items-center">
    @csrf @method('PUT')
    <select name="status" class="form-select form-select-sm w-auto" aria-label="Order status">
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <select name="payment_status" class="form-select form-select-sm w-auto" aria-label="Payment status">
        @foreach ($paymentStatuses as $paymentStatus)
            <option value="{{ $paymentStatus->value }}" @selected($order->payment_status === $paymentStatus)>{{ $paymentStatus->label() }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm btn-brand"><i class="bi bi-check-lg"></i> Save</button>
</form>

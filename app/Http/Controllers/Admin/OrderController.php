<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderUpdateRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** List orders (search + status / payment filters, paginated). */
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->search($request->string('q')->toString())
            ->when(OrderStatus::tryFrom((string) $request->query('status')), fn ($query, $status) => $query->where('status', $status))
            ->when(PaymentStatus::tryFrom((string) $request->query('payment_status')), fn ($query, $status) => $query->where('payment_status', $status))
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }

    /** Order details. */
    public function show(Order $order): View
    {
        $order->load('product');

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }

    /** Change order status and/or payment status. */
    public function update(OrderUpdateRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->validated());

        return redirect()
            ->back(fallback: route('admin.orders.index'))
            ->with('success', "Order {$order->order_number} updated: {$order->status->label()} / {$order->payment_status->label()}.");
    }

    /** Delete an order. */
    public function destroy(Order $order): RedirectResponse
    {
        $number = $order->order_number;
        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', "Order {$number} was deleted.");
    }
}

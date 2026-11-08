<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

/**
 * Public "Order Now" flow.
 */
class OrderController extends Controller
{
    /** OrderService contains the pricing / order creation rules. */
    public function __construct(private readonly OrderService $orders) {}

    /** Save the order and show the confirmation page. */
    public function store(PlaceOrderRequest $request, Product $product): RedirectResponse
    {
        $order = $this->orders->place($product, $request->validated());

        return redirect()->route('shop.order.thanks', $order->tracking_token);
    }

    /** Order confirmation ("thank you") page. */
    public function thanks(Order $order): Response
    {
        return $this->sensitive(response()->view('shop.order-thanks', compact('order')));
    }
}

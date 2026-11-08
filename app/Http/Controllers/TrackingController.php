<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackOrderRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Public order tracking: the customer enters the token shown after ordering.
 */
class TrackingController extends Controller
{
    /** Tracking form. */
    public function create(): View
    {
        return view('shop.track', ['order' => null]);
    }

    /** Look the token up and redirect to the shareable tracking URL. */
    public function lookup(TrackOrderRequest $request): RedirectResponse
    {
        $order = Order::where('tracking_token', $request->validated('token'))->first();

        if (! $order) {
            return back()
                ->withInput()
                ->withErrors(['token' => 'We could not find an order with that token. Please check it and try again.']);
        }

        return redirect()->route('shop.track.show', $order->tracking_token);
    }

    /** Tracking result page. */
    public function show(Order $order): Response
    {
        return $this->sensitive(response()->view('shop.track', compact('order')));
    }
}

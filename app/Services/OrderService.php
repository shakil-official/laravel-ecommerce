<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;

/**
 * Creates orders from the public "Order Now" form.
 */
class OrderService
{
    /**
     * @param  array{customer_name:string, phone:string, email?:?string, address:string, note?:?string, quantity:int|string, payment_method:string}  $data
     */
    public function place(Product $product, array $data): Order
    {
        $quantity = (int) $data['quantity'];
        $unitPrice = (float) $product->new_price;

        return Order::create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total' => round($unitPrice * $quantity, 2),
            'customer_name' => $data['customer_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'],
            'note' => $data['note'] ?? null,
            'payment_method' => $data['payment_method'],
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
        ]);
    }
}

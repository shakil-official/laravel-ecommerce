<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seeds a few demo orders in different statuses so the admin order list is not
 * empty. Needs the demo catalogue; idempotent (matched by order number).
 */
class DemoOrderSeeder extends Seeder
{
    /** Create demo orders in different statuses (tokens DEMO-0001-TRK1 ... DEMO-0006-TRK6). */
    public function run(): void
    {
        $rows = require __DIR__.'/data/orders.php';

        foreach ($rows as [$number, $token, $productName, $customer, $phone, $address, $quantity, $method, $status, $paymentStatus]) {
            $product = Product::where('name', $productName)->first();

            if (! $product || Order::where('order_number', $number)->exists()) {
                continue;
            }

            Order::create([
                'order_number' => $number,
                'tracking_token' => $token,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->new_price,
                'quantity' => $quantity,
                'total' => round((float) $product->new_price * $quantity, 2),
                'customer_name' => $customer,
                'phone' => $phone,
                'address' => $address,
                'payment_method' => $method,
                'status' => $status,
                'payment_status' => $paymentStatus,
            ]);
        }
    }
}

<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /** Default state: a pending, unpaid cash-on-delivery order. */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 100, 5000);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'unit_price' => $price,
            'quantity' => $quantity,
            'total' => round($price * $quantity, 2),
            'customer_name' => fake()->name(),
            'phone' => '017'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'note' => null,
            'payment_method' => PaymentMethod::Cod,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
        ];
    }

    /** State: order with the given status. */
    public function status(OrderStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    /** State: payment_status = paid. */
    public function paid(): static
    {
        return $this->state(fn () => ['payment_status' => PaymentStatus::Paid]);
    }
}

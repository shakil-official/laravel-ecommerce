<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the table / column. */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();

            // Deleting a product keeps the order (snapshot columns below remain).
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 150);
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total', 12, 2);

            $table->string('customer_name', 100);
            $table->string('phone', 20);
            $table->string('email', 150)->nullable();
            $table->text('address');
            $table->text('note')->nullable();

            $table->string('payment_method', 20)->default('cod');
            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_status', 20)->default('unpaid')->index();

            $table->timestamps();
        });
    }

    /** Reverse the migration. */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

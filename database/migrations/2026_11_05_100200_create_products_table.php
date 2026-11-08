<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the table / column. */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('subcategory_id')->constrained('subcategories')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->text('description');
            $table->string('image')->nullable();
            $table->decimal('old_price', 10, 2);
            $table->decimal('new_price', 10, 2);
            $table->timestamps();

            $table->index(['subcategory_id', 'created_at']);
        });
    }

    /** Reverse the migration. */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

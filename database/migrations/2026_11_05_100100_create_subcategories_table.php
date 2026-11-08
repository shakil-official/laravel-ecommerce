<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the table / column. */
    public function up(): void
    {
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            // Deleting a category removes its subcategories at database level.
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            // The same sub-category name may exist under different categories,
            // but not twice under the same one.
            $table->unique(['category_id', 'name']);
        });
    }

    /** Reverse the migration. */
    public function down(): void
    {
        Schema::dropIfExists('subcategories');
    }
};

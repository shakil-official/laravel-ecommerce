<?php

namespace Tests\Unit\Services;

use App\Services\ProductImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_store_saves_file_in_products_directory(): void
    {
        $path = (new ProductImageService)->store(UploadedFile::fake()->image('a.jpg'));

        $this->assertStringStartsWith('products/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_replace_stores_new_and_removes_old_file(): void
    {
        Storage::disk('public')->put('products/old.jpg', 'old');

        $new = (new ProductImageService)->replace('products/old.jpg', UploadedFile::fake()->image('b.jpg'));

        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($new);
    }

    public function test_replace_works_when_there_was_no_old_image(): void
    {
        $new = (new ProductImageService)->replace(null, UploadedFile::fake()->image('c.jpg'));

        Storage::disk('public')->assertExists($new);
    }

    public function test_delete_removes_file_and_ignores_null(): void
    {
        Storage::disk('public')->put('products/z.jpg', 'x');
        $service = new ProductImageService;

        $service->delete('products/z.jpg');
        $service->delete(null);

        Storage::disk('public')->assertMissing('products/z.jpg');
    }
}

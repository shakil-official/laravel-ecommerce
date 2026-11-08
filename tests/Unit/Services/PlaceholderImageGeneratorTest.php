<?php

namespace Tests\Unit\Services;

use App\Services\PlaceholderImageGenerator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlaceholderImageGeneratorTest extends TestCase
{
    public function test_is_available_reflects_gd_extension(): void
    {
        $this->assertSame(function_exists('imagecreatetruecolor'), (new PlaceholderImageGenerator)->isAvailable());
    }

    public function test_generate_creates_a_jpeg_on_the_public_disk(): void
    {
        if (! (new PlaceholderImageGenerator)->isAvailable()) {
            $this->markTestSkipped('GD extension is not installed.');
        }

        Storage::fake('public');

        $path = (new PlaceholderImageGenerator)->generate('Galaxy Nova 5G');

        $this->assertStringStartsWith('products/galaxy-nova-5g-', $path);
        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertNotFalse(getimagesizefromstring(Storage::disk('public')->get($path)));
    }

    public function test_render_returns_valid_jpeg_bytes_and_is_deterministic(): void
    {
        $generator = new PlaceholderImageGenerator;

        if (! $generator->isAvailable()) {
            $this->markTestSkipped('GD extension is not installed.');
        }

        $bytes = $generator->render('Same Label');

        $this->assertSame([800, 600], array_slice(getimagesizefromstring($bytes), 0, 2));
        $this->assertSame($bytes, $generator->render('Same Label'));
    }

    public function test_hsl_to_rgb_converts_primary_colours(): void
    {
        $generator = new PlaceholderImageGenerator;

        $this->assertSame([255, 0, 0], $generator->hslToRgb(0, 1.0, 0.5));
        $this->assertSame([0, 255, 0], $generator->hslToRgb(120, 1.0, 0.5));
        $this->assertSame([0, 0, 255], $generator->hslToRgb(240, 1.0, 0.5));
        $this->assertSame([255, 255, 255], $generator->hslToRgb(10, 1.0, 1.0));
    }
}

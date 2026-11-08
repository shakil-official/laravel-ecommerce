<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Generates a coloured gradient JPEG for demo/seed products so the storefront
 * looks good without downloading any images. Colours are derived from the
 * label, so the same product always gets the same colours.
 */
class PlaceholderImageGenerator
{
    /** True when the PHP GD extension is installed. */
    public function isAvailable(): bool
    {
        return function_exists('imagecreatetruecolor') && function_exists('imagejpeg');
    }

    /**
     * Create the image on the "public" disk and return its relative path,
     * or null when the GD extension is not installed.
     */
    public function generate(string $label, string $directory = ProductImageService::DIRECTORY): ?string
    {
        $bytes = $this->render($label);

        if ($bytes === null) {
            return null;
        }

        $path = trim($directory, '/').'/'.Str::slug($label).'-'.Str::lower(Str::random(6)).'.jpg';
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

    /**
     * Render the JPEG bytes (800x600 gradient) or null when GD is missing.
     */
    public function render(string $label): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $width = 800;
        $height = 600;
        $hue = crc32($label) % 360;

        [$r1, $g1, $b1] = $this->hslToRgb($hue, 0.60, 0.58);
        [$r2, $g2, $b2] = $this->hslToRgb(($hue + 45) % 360, 0.65, 0.34);

        $image = imagecreatetruecolor($width, $height);

        // Vertical gradient.
        for ($y = 0; $y < $height; $y++) {
            $t = $y / ($height - 1);
            $color = imagecolorallocate(
                $image,
                (int) ($r1 + ($r2 - $r1) * $t),
                (int) ($g1 + ($g2 - $g1) * $t),
                (int) ($b1 + ($b2 - $b1) * $t)
            );
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Soft decorative circles.
        imagealphablending($image, true);
        $light = imagecolorallocatealpha($image, 255, 255, 255, 105);
        imagefilledellipse($image, (int) ($width * 0.72), (int) ($height * 0.38), 360, 360, $light);
        imagefilledellipse($image, (int) ($width * 0.28), (int) ($height * 0.72), 240, 240, $light);

        ob_start();
        imagejpeg($image, null, 85);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    public function hslToRgb(int $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return [
            (int) round(($r + $m) * 255),
            (int) round(($g + $m) * 255),
            (int) round(($b + $m) * 255),
        ];
    }
}

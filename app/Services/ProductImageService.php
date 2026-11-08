<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Small wrapper around the "public" disk so controllers never deal with paths.
 */
class ProductImageService
{
    public const DIRECTORY = 'products';

    /** Store an uploaded image and return its path relative to the disk root. */
    public function store(UploadedFile $file): string
    {
        return $file->store(self::DIRECTORY, 'public');
    }

    /** Replace $oldPath with a new upload; the old file is removed. */
    public function replace(?string $oldPath, UploadedFile $file): string
    {
        $newPath = $this->store($file);
        $this->delete($oldPath);

        return $newPath;
    }

    /** Delete a stored file; null / empty paths are ignored. */
    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}

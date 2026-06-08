<?php

declare(strict_types=1);

namespace App\Services\Storage;

interface StorageInterface
{
    /** Writes contents to the path; returns the public URL. */
    public function put(string $path, string $contents): string;

    /** Returns the public URL for a stored path. */
    public function url(string $path): string;

    public function exists(string $path): bool;

    public function delete(string $path): bool;

    /** Absolute filesystem path (used by the image processor for re-encoding). */
    public function absolutePath(string $path): string;
}

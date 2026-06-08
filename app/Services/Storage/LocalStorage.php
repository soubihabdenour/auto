<?php

declare(strict_types=1);

namespace App\Services\Storage;

final class LocalStorage implements StorageInterface
{
    public function __construct(
        private string $baseDir,
        private string $publicUrl,
    ) {}

    public function put(string $path, string $contents): string
    {
        $full = $this->absolutePath($path);
        $dir  = dirname($full);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($full, $contents, LOCK_EX);
        @chmod($full, 0644);
        return $this->url($path);
    }

    public function url(string $path): string
    {
        return rtrim($this->publicUrl, '/') . '/' . ltrim($path, '/');
    }

    public function exists(string $path): bool
    {
        return is_file($this->absolutePath($path));
    }

    public function delete(string $path): bool
    {
        $full = $this->absolutePath($path);
        if (! is_file($full)) return true;
        return @unlink($full);
    }

    public function absolutePath(string $path): string
    {
        return rtrim($this->baseDir, '/') . '/' . ltrim($path, '/');
    }
}

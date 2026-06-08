<?php

declare(strict_types=1);

namespace App\Services\Image;

use App\Services\Storage\StorageInterface;
use RuntimeException;

/**
 * Processes uploaded images:
 *   - validates mime (jpeg/png/webp)
 *   - strips EXIF by re-encoding
 *   - generates three sized variants (1600 / 800 / 400)
 *   - stores via the injected StorageInterface and returns the
 *     primary (large) path, plus the dimensions and bytesize.
 *
 * Output paths use the convention:
 *   vehicles/{vehicleId}/{role}/{filename}.jpg
 *
 * Only JPEG output for v1 — the gallery serves the same path at
 * three sizes, swappable to WebP/AVIF in Phase 4 without touching
 * the controllers.
 */
final class ImageProcessor
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_BYTES    = 12 * 1024 * 1024; // 12 MB raw upload

    private const SIZES = [
        'large'  => 1600,
        'medium' => 800,
        'thumb'  => 400,
    ];

    public function __construct(private StorageInterface $storage) {}

    /**
     * @param string $tmpFile  path to the uploaded temp file
     * @param string $mime     the *trusted* mime (re-detected via getimagesize)
     * @param int    $vehicleId  for output path
     *
     * @return array{path:string, large_url:string, medium_url:string, thumb_url:string,
     *               width:int, height:int, size_bytes:int}
     */
    public function processVehicleImage(string $tmpFile, string $mime, int $vehicleId): array
    {
        $size = (int) @filesize($tmpFile);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new RuntimeException('Image too large or empty.');
        }

        // Re-detect mime via getimagesize (don't trust the client)
        $info = @getimagesize($tmpFile);
        if ($info === false) {
            throw new RuntimeException('Could not read image.');
        }
        $detectedMime = (string) ($info['mime'] ?? '');
        if (! in_array($detectedMime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException("Unsupported image type: {$detectedMime}");
        }

        $src = $this->loadGd($tmpFile, $detectedMime);
        [$origW, $origH] = [imagesx($src), imagesy($src)];

        $token  = substr(bin2hex(random_bytes(4)), 0, 8);
        $base   = "vehicles/{$vehicleId}";
        $stored = [];

        foreach (self::SIZES as $role => $maxW) {
            $resized = $this->fitWidth($src, min($maxW, $origW));

            // JPEG (universal fallback)
            ob_start();
            imagejpeg($resized, null, 86);
            $jpegBlob = (string) ob_get_clean();
            $jpegPath = "{$base}/{$role}/{$token}.jpg";
            $this->storage->put($jpegPath, $jpegBlob);

            // WebP (preferred by modern browsers, ~25% smaller)
            if (function_exists('imagewebp')) {
                ob_start();
                @imagewebp($resized, null, 82);
                $webpBlob = (string) ob_get_clean();
                if ($webpBlob !== '') {
                    $this->storage->put("{$base}/{$role}/{$token}.webp", $webpBlob);
                }
            }

            imagedestroy($resized);
            $stored[$role] = $jpegPath;
        }

        // Re-encoded original (max 2400 wide, jpeg quality 92) — used by lightbox
        $orig = $this->fitWidth($src, min(2400, $origW));
        ob_start(); imagejpeg($orig, null, 92);
        $origBlob = (string) ob_get_clean();
        imagedestroy($orig);
        $origPath = "{$base}/orig/{$token}.jpg";
        $this->storage->put($origPath, $origBlob);

        imagedestroy($src);

        // The "path" written to vehicle_images is the role we want callers to
        // hit by default. Phase 4 will switch the partial to <picture srcset>.
        return [
            'path'       => $stored['large'],
            'large_url'  => $this->storage->url($stored['large']),
            'medium_url' => $this->storage->url($stored['medium']),
            'thumb_url'  => $this->storage->url($stored['thumb']),
            'width'      => imagesx($this->loadGd($this->storage->absolutePath($stored['large']), 'image/jpeg')),
            'height'     => imagesy($this->loadGd($this->storage->absolutePath($stored['large']), 'image/jpeg')),
            'size_bytes' => (int) filesize($this->storage->absolutePath($stored['large'])),
        ];
    }

    private function loadGd(string $file, string $mime): \GdImage
    {
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file),
            'image/png'  => @imagecreatefrompng($file),
            'image/webp' => @imagecreatefromwebp($file),
            default      => false,
        };
        if (! $img instanceof \GdImage) {
            throw new RuntimeException("Failed to decode image as {$mime}.");
        }
        return $img;
    }

    private function fitWidth(\GdImage $src, int $targetWidth): \GdImage
    {
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        if ($srcW <= $targetWidth) {
            // Make a copy at original size to avoid double-free
            $out = imagecreatetruecolor($srcW, $srcH);
            imagecopy($out, $src, 0, 0, 0, 0, $srcW, $srcH);
            return $out;
        }
        $targetHeight = (int) round($srcH * ($targetWidth / $srcW));
        $out = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($out, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcW, $srcH);
        return $out;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

final class SlugGenerator
{
    /**
     * Build a vehicle slug from canonical parts. Falls back to a random suffix
     * when no VIN is available.
     *
     * Example: "2022-hyundai-tucson-diesel-7a3f"
     */
    public static function forVehicle(
        int    $year,
        string $brand,
        string $model,
        string $fuelType = '',
        ?string $vin = null,
    ): string {
        $parts = [
            (string) $year,
            self::slugify($brand),
            self::slugify($model),
        ];
        if ($fuelType !== '') {
            $parts[] = self::slugify($fuelType);
        }
        $suffix = $vin !== null && strlen($vin) >= 4
            ? substr(strtolower(preg_replace('/[^A-Za-z0-9]/', '', $vin) ?? ''), -4)
            : substr(bin2hex(random_bytes(2)), 0, 4);
        if ($suffix !== '') {
            $parts[] = $suffix;
        }
        return implode('-', array_filter($parts, fn ($p) => $p !== ''));
    }

    public static function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value));
        // Transliterate a few accented Latin chars
        $value = strtr($value, [
            'à'=>'a','á'=>'a','â'=>'a','ä'=>'a','ã'=>'a','å'=>'a',
            'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
            'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i',
            'ò'=>'o','ó'=>'o','ô'=>'o','ö'=>'o','õ'=>'o','ø'=>'o',
            'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u',
            'ñ'=>'n','ç'=>'c','ß'=>'ss',
        ]);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
        return trim($value, '-');
    }
}

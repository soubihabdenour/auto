<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Lightweight phone helpers. Not a substitute for libphonenumber, but
 * "good enough" for the Algerian context (most leads = +213 or 0X).
 */
final class Phone
{
    /**
     * Normalize to a best-effort international form. Examples:
     *   "0555 11 22 33"   → "+213555112233"
     *   "+213 555-112233" → "+213555112233"
     *   "555 112 233"     → "+213555112233" (assumes default country)
     */
    public static function normalize(string $input, string $defaultCountryCode = '213'): string
    {
        $digits = preg_replace('/[^\d+]/', '', $input) ?? '';
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '+')) {
            return $digits;
        }
        if (str_starts_with($digits, '00')) {
            return '+' . substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            return '+' . $defaultCountryCode . substr($digits, 1);
        }
        // Bare 8–10 digits → assume default country
        return '+' . $defaultCountryCode . $digits;
    }

    /** For wa.me links: only digits, no leading +. */
    public static function forWhatsapp(string $input, string $defaultCountryCode = '213'): string
    {
        $normalized = self::normalize($input, $defaultCountryCode);
        return ltrim($normalized, '+');
    }
}

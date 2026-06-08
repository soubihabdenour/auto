<?php

declare(strict_types=1);

namespace App\Services\I18n;

use App\Core\Request;

final class LocaleResolver
{
    public function __construct(
        /** @var array<int, string> */
        private array  $available,
        private string $default,
    ) {}

    public function resolve(Request $request): string
    {
        // 1. Explicit URL route param wins (set by router for /{locale}/...)
        $fromRoute = $request->route('locale');
        if ($fromRoute !== null && $this->isSupported($fromRoute)) {
            return $fromRoute;
        }

        // 2. Cookie
        $fromCookie = $request->cookies['locale'] ?? null;
        if (is_string($fromCookie) && $this->isSupported($fromCookie)) {
            return $fromCookie;
        }

        // 3. Accept-Language header
        $accept = $request->header('Accept-Language');
        if (is_string($accept) && $accept !== '') {
            foreach (explode(',', $accept) as $chunk) {
                $code = strtolower(substr(trim($chunk), 0, 2));
                if ($this->isSupported($code)) {
                    return $code;
                }
            }
        }

        return $this->default;
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->available, true);
    }

    /** @return array<int, string> */
    public function available(): array
    {
        return $this->available;
    }

    public function default(): string
    {
        return $this->default;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\I18n;

final class Translator
{
    private string $locale;
    private string $fallback;
    private string $langPath;

    /** @var array<string, array<string, mixed>>  cache: locale => merged keys */
    private array $loaded = [];

    public function __construct(string $langPath, string $defaultLocale, string $fallbackLocale)
    {
        $this->langPath = rtrim($langPath, '/');
        $this->locale   = $defaultLocale;
        $this->fallback = $fallbackLocale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * Translate a dot-notated key, e.g. "common.cta.browse_cars".
     * @param array<string, scalar> $replace
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $value = $this->raw($key, $locale);
        if (! is_string($value)) {
            return $key;
        }
        return $this->interpolate($value, $replace);
    }

    /** Return a nested array for keys like 'pages.process.steps'. Empty array if missing. */
    public function getArray(string $key, ?string $locale = null): array
    {
        $value = $this->raw($key, $locale);
        return is_array($value) ? $value : [];
    }

    /** Lookup with fallback; returns whatever type the bag holds (string|array|null). */
    private function raw(string $key, ?string $locale = null): mixed
    {
        $locale = $locale ?? $this->locale;
        $value  = $this->lookup($key, $locale);
        if ($value === null && $locale !== $this->fallback) {
            $value = $this->lookup($key, $this->fallback);
        }
        return $value;
    }

    private function lookup(string $key, string $locale): mixed
    {
        $segments = explode('.', $key);
        if (count($segments) < 2) {
            return null; // require at least namespace.key
        }
        $namespace = array_shift($segments);
        $bag = $this->loadNamespace($locale, $namespace);
        $value = $bag;
        foreach ($segments as $seg) {
            if (! is_array($value) || ! array_key_exists($seg, $value)) {
                return null;
            }
            $value = $value[$seg];
        }
        return $value;
    }

    /** @return array<string, mixed> */
    private function loadNamespace(string $locale, string $namespace): array
    {
        $cacheKey = $locale . '|' . $namespace;
        if (isset($this->loaded[$cacheKey])) {
            return $this->loaded[$cacheKey];
        }
        $file = $this->langPath . '/' . $locale . '/' . $namespace . '.php';
        $data = is_file($file) ? (require $file) : [];
        $this->loaded[$cacheKey] = is_array($data) ? $data : [];
        return $this->loaded[$cacheKey];
    }

    /** @param array<string, scalar> $replace */
    private function interpolate(string $value, array $replace): string
    {
        if ($replace === []) {
            return $value;
        }
        $search = [];
        $vals   = [];
        foreach ($replace as $k => $v) {
            $search[] = ':' . $k;
            $vals[]   = (string) $v;
        }
        return str_replace($search, $vals, $value);
    }
}

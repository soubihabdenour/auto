<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Config holds the merged result of every file in /config.
 * Access via dot-notation: $config->get('app.debug').
 */
final class Config
{
    /** @var array<string, mixed> */
    private array $items = [];

    public function __construct(string $configDir)
    {
        foreach (glob($configDir . '/*.php') ?: [] as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;
        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $ref = &$this->items;
        foreach ($segments as $segment) {
            if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }
        $ref = $value;
    }

    public function all(): array
    {
        return $this->items;
    }
}

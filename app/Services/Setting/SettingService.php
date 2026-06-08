<?php

declare(strict_types=1);

namespace App\Services\Setting;

use App\Repositories\SettingRepository;

/**
 * Loads settings from DB once per request and caches them in-memory.
 * Cast values to typed PHP based on the row's `type` column.
 */
final class SettingService
{
    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    public function __construct(private SettingRepository $repo) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureLoaded();
        return $this->cache[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        $this->ensureLoaded();
        return $this->cache ?? [];
    }

    private function ensureLoaded(): void
    {
        if ($this->cache !== null) {
            return;
        }
        $this->cache = [];
        try {
            foreach ($this->repo->all() as $row) {
                $this->cache[$row['key']] = $this->cast($row['value'], $row['type']);
            }
        } catch (\Throwable) {
            // DB not yet provisioned — settings remain empty (callers should use defaults).
            $this->cache = [];
        }
    }

    private function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int'   => (int) $value,
            'float' => (float) $value,
            'bool'  => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'  => json_decode((string) $value, true) ?? null,
            default => (string) $value,
        };
    }
}

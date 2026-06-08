<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env loader. Parses KEY=VALUE lines; supports quoted values and # comments.
 * Does NOT overwrite vars already set in the real environment.
 */
final class Env
{
    public static function load(string $path): void
    {
        if (! is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $key = trim($key);
            if ($key === '') {
                continue;
            }

            // Quoted values: preserve content verbatim (inline # is part of value)
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                    if (getenv($key) === false) {
                        putenv("{$key}={$value}");
                        $_ENV[$key]    = $value;
                        $_SERVER[$key] = $value;
                    }
                    continue;
                }
            }

            // Unquoted: strip inline " # comment" (whitespace + # to end of line)
            if (preg_match('/^(.*?)\s+#/', $value, $m)) {
                $value = trim($m[1]);
            }

            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

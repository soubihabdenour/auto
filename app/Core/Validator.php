<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal validator. Rule syntax: 'field' => 'required|string|max:255|email'.
 *
 * Supported rules:
 *   required, nullable, string, int, numeric, bool,
 *   min:N (string length or numeric value),
 *   max:N (string length or numeric value),
 *   email, url, in:a,b,c, regex:/.../,
 *   phone (lax E.164-ish), slug, locale (in available locales),
 *   honeypot (must be empty)
 */
final class Validator
{
    /** @var array<string, string[]> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $validated = [];

    /**
     * @param array<string, mixed>  $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages  field.rule => message
     */
    public function __construct(
        private array $data,
        private array $rules,
        private array $messages = [],
    ) {}

    public function passes(): bool
    {
        foreach ($this->rules as $field => $ruleset) {
            $rules = is_array($ruleset) ? $ruleset : explode('|', $ruleset);
            $value = $this->data[$field] ?? null;

            $nullable = in_array('nullable', $rules, true);
            if (($value === null || $value === '') && $nullable) {
                $this->validated[$field] = null;
                continue;
            }
            foreach ($rules as $rule) {
                if ($rule === 'nullable') continue;
                if (! $this->check($field, $value, $rule)) {
                    // stop on first failure per field
                    break;
                }
            }
            if (! isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }
        return $this->errors === [];
    }

    /** @return array<string, string[]> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        return $this->validated;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function check(string $field, mixed $value, string $rule): bool
    {
        [$name, $param] = $this->parseRule($rule);

        $ok = match ($name) {
            'required' => $value !== null && $value !== '' && $value !== [],
            'string'   => is_string($value),
            'int'      => is_int($value) || (is_string($value) && ctype_digit(ltrim($value, '-'))),
            'numeric'  => is_numeric($value),
            'bool'     => is_bool($value) || in_array($value, ['0', '1', 0, 1, 'true', 'false', true, false], true),
            'min'      => $this->checkSize($value, '>=', (float) $param),
            'max'      => $this->checkSize($value, '<=', (float) $param),
            'email'    => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url'      => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false,
            'in'       => $param !== null && in_array((string) $value, explode(',', $param), true),
            'regex'    => $param !== null && is_string($value) && preg_match($param, $value) === 1,
            'phone'    => is_string($value) && preg_match('/^\+?[0-9 ()\-]{7,20}$/', $value) === 1,
            'slug'     => is_string($value) && preg_match('/^[a-z0-9-]+$/', $value) === 1,
            'locale'   => is_string($value) && in_array($value, (array) config('locales.available', []), true),
            'honeypot' => $value === null || $value === '',
            default    => true, // unknown rules pass (don't break)
        };

        if (! $ok) {
            $this->errors[$field][] = $this->message($field, $name, $param);
        }
        return $ok;
    }

    /** @return array{0:string,1:?string} */
    private function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$n, $p] = explode(':', $rule, 2);
            return [$n, $p];
        }
        return [$rule, null];
    }

    private function checkSize(mixed $value, string $op, float $bound): bool
    {
        $size = is_numeric($value) ? (float) $value : (is_string($value) ? mb_strlen($value) : 0);
        return match ($op) {
            '>=' => $size >= $bound,
            '<=' => $size <= $bound,
            default => false,
        };
    }

    private function message(string $field, string $rule, ?string $param): string
    {
        // Allow custom override
        $key = $field . '.' . $rule;
        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }
        // Try translation: validation.{rule} with :field placeholder
        try {
            $tpl = t('validation.' . $rule, [
                'field' => $field,
                'param' => (string) ($param ?? ''),
            ]);
            if ($tpl !== 'validation.' . $rule) {
                return $tpl;
            }
        } catch (\Throwable) {
            // Translator may not be available (e.g. during tests)
        }
        return match ($rule) {
            'required' => "The {$field} field is required.",
            'email'    => "The {$field} must be a valid email.",
            'phone'    => "The {$field} must be a valid phone number.",
            'min'      => "The {$field} must be at least {$param}.",
            'max'      => "The {$field} may not be greater than {$param}.",
            'in'       => "The {$field} is invalid.",
            'honeypot' => 'Bot trap triggered.',
            default    => "The {$field} field is invalid ({$rule}).",
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Lead;

/**
 * Renders the "I want to source a vehicle" form fields into a single
 * free-text message that gets stored on the lead and emailed to admins.
 */
final class RequestVehicleMessageFormatter
{
    /** @var list<string> Fields rendered as "key: value" in declaration order. */
    private const FIELDS = ['brand', 'year_min', 'year_max', 'budget_usd', 'fuel', 'transmission'];

    /** @param array<string, mixed> $data */
    public static function format(array $data): string
    {
        $bits = [];
        foreach (self::FIELDS as $key) {
            if (! empty($data[$key])) {
                $bits[] = $key . ': ' . $data[$key];
            }
        }
        if (! empty($data['notes'])) {
            $bits[] = 'notes: ' . $data['notes'];
        }
        return implode("\n", $bits);
    }
}

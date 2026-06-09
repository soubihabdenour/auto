<?php

declare(strict_types=1);

namespace App\Services\Vehicle;

use RuntimeException;

/**
 * Calls the NHTSA vPIC public API to turn a VIN into a set of hints we can
 * pre-fill into the admin vehicle form.
 *
 * Endpoint: https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVin/{VIN}?format=json
 * Free, no API key. Works well for Hyundai/Kia/Genesis (US-homologated).
 *
 * Returns null fields where the data wasn't available or couldn't be normalized
 * to our enum values, so the controller can leave the corresponding form field alone.
 */
final class VinDecoder
{
    private const ENDPOINT  = 'https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVin/';
    private const TIMEOUT_S = 6;

    /**
     * @return array{
     *   vin:          string,
     *   raw_make:     ?string,
     *   raw_model:    ?string,
     *   year:         ?int,
     *   fuel_type:    ?string,
     *   transmission: ?string,
     *   drivetrain:   ?string,
     *   engine_cc:    ?int,
     *   engine_power_hp: ?int,
     *   doors:        ?int,
     *   seats:        ?int,
     * }
     */
    public function decode(string $vin): array
    {
        $vin = strtoupper(trim($vin));
        if (! preg_match('/^[A-HJ-NPR-Z0-9]{11,17}$/', $vin)) {
            throw new RuntimeException('VIN must be 11–17 alphanumeric characters (no I, O, Q).');
        }

        $url     = self::ENDPOINT . rawurlencode($vin) . '?format=json';
        $payload = $this->fetch($url);
        $rows    = $this->indexByVariable($payload['Results'] ?? []);

        // NHTSA returns "ErrorCode" as "0" for valid decodes. Anything else
        // is partial / failed — we still return what we got but log via the
        // raw_make field being null.
        $rawMake  = $this->str($rows['Make'] ?? null);
        $rawModel = $this->str($rows['Model'] ?? null);

        return [
            'vin'             => $vin,
            'raw_make'        => $rawMake,
            'raw_model'       => $rawModel,
            'year'            => $this->int($rows['Model Year'] ?? null),
            'fuel_type'       => $this->normalizeFuel($this->str($rows['Fuel Type - Primary'] ?? null)),
            'transmission'    => $this->normalizeTransmission($this->str($rows['Transmission Style'] ?? null)),
            'drivetrain'      => $this->normalizeDrivetrain($this->str($rows['Drive Type'] ?? null)),
            'engine_cc'       => $this->engineCc($rows),
            'engine_power_hp' => $this->int($rows['Engine Brake (hp) From'] ?? $rows['Engine Brake (hp)'] ?? null),
            'doors'           => $this->int($rows['Doors'] ?? null),
            'seats'           => $this->int($rows['Seat Belts All'] ?? null),
        ];
    }

    // ---------- HTTP ----------

    /** @return array<string, mixed> */
    private function fetch(string $url): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Could not initialise HTTP client.');
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT_S,
            CURLOPT_CONNECTTIMEOUT => self::TIMEOUT_S,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_USERAGENT      => 'KAE-VinDecoder/1.0',
        ]);
        $body   = curl_exec($ch);
        $errno  = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close() removed — no-op since PHP 8.0 and deprecated in 8.5.

        if ($errno !== 0 || $body === false) {
            throw new RuntimeException('VIN decode service unreachable.');
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("VIN decode service returned HTTP {$status}.");
        }
        $decoded = json_decode((string) $body, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('VIN decode service returned malformed JSON.');
        }
        return $decoded;
    }

    // ---------- Helpers ----------

    /**
     * @param  array<int, array<string, mixed>> $results
     * @return array<string, string|null>
     */
    private function indexByVariable(array $results): array
    {
        $out = [];
        foreach ($results as $row) {
            $name  = isset($row['Variable']) ? (string) $row['Variable'] : null;
            $value = $row['Value'] ?? null;
            if ($name !== null) {
                $out[$name] = $value === null || $value === '' || $value === 'Not Applicable'
                    ? null
                    : (string) $value;
            }
        }
        return $out;
    }

    private function str(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        return $v === '' ? null : $v;
    }

    private function int(?string $v): ?int
    {
        if ($v === null) return null;
        if (! preg_match('/-?\d+/', $v, $m)) return null;
        $n = (int) $m[0];
        return $n > 0 ? $n : null;
    }

    /** @param array<string, string|null> $rows */
    private function engineCc(array $rows): ?int
    {
        $cc = $this->int($rows['Displacement (CC)'] ?? null);
        if ($cc !== null) return $cc;
        // Fall back to liters → cc
        $l = $rows['Displacement (L)'] ?? null;
        if ($l !== null && is_numeric($l)) {
            return (int) round(((float) $l) * 1000);
        }
        return null;
    }

    private function normalizeFuel(?string $v): ?string
    {
        if ($v === null) return null;
        $v = strtolower($v);
        return match (true) {
            str_contains($v, 'plug-in') || str_contains($v, 'plug in')         => 'phev',
            str_contains($v, 'hybrid')                                         => 'hybrid',
            str_contains($v, 'electric') || str_contains($v, 'battery')        => 'electric',
            str_contains($v, 'diesel')                                         => 'diesel',
            str_contains($v, 'gasoline') || str_contains($v, 'petrol')         => 'petrol',
            str_contains($v, 'lpg') || str_contains($v, 'liquefied petroleum') => 'lpg',
            default                                                            => null,
        };
    }

    private function normalizeTransmission(?string $v): ?string
    {
        if ($v === null) return null;
        $v = strtolower($v);
        return match (true) {
            str_contains($v, 'cvt') || str_contains($v, 'continuously variable') => 'cvt',
            str_contains($v, 'dual-clutch') || str_contains($v, 'dual clutch')
                || str_contains($v, 'dct')                                       => 'dct',
            str_contains($v, 'manual')                                           => 'manual',
            str_contains($v, 'automatic') || str_contains($v, 'automated')       => 'automatic',
            default                                                              => null,
        };
    }

    private function normalizeDrivetrain(?string $v): ?string
    {
        if ($v === null) return null;
        $v = strtolower($v);
        return match (true) {
            str_contains($v, 'awd') || str_contains($v, 'all-wheel')   => 'awd',
            str_contains($v, '4wd') || str_contains($v, 'four-wheel')
                || str_contains($v, '4x4')                             => '4wd',
            str_contains($v, 'rwd') || str_contains($v, 'rear-wheel')  => 'rwd',
            str_contains($v, 'fwd') || str_contains($v, 'front-wheel') => 'fwd',
            default                                                    => null,
        };
    }
}

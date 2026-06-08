<?php

declare(strict_types=1);

namespace App\Services\Vehicle;

/**
 * Enumerations and validation rules for the admin vehicle form.
 * Kept as a single source of truth so the controller, view, and writer
 * all agree on what's valid.
 */
final class VehicleFormRules
{
    public const STATUSES      = ['draft', 'available', 'reserved', 'sold', 'archived'];
    public const FUEL_TYPES    = ['petrol', 'diesel', 'hybrid', 'phev', 'electric', 'lpg'];
    public const TRANSMISSIONS = ['manual', 'automatic', 'dct', 'cvt'];
    public const DRIVETRAINS   = ['fwd', 'rwd', 'awd', '4wd'];
    public const ACCIDENT      = ['none', 'minor', 'major', 'unknown'];

    /** @return array<string, string> */
    public static function validatorRules(): array
    {
        return [
            'brand_id'        => 'required|int',
            'model_id'        => 'required|int',
            'year'            => 'required|int|min:1980|max:2100',
            'mileage_km'      => 'required|numeric|min:0|max:1000000',
            'transmission'    => 'required|in:' . implode(',', self::TRANSMISSIONS),
            'fuel_type'       => 'required|in:' . implode(',', self::FUEL_TYPES),
            'drivetrain'      => 'nullable|in:' . implode(',', self::DRIVETRAINS),
            'price_usd'       => 'required|numeric|min:0|max:9999999',
            'status'          => 'required|in:' . implode(',', self::STATUSES),
            'body_type_id'    => 'nullable|int',
            'vin'             => 'nullable|string|max:40',
            'engine_cc'       => 'nullable|int',
            'engine_power_hp' => 'nullable|int',
            'exterior_color'  => 'nullable|string|max:60',
            'interior_color'  => 'nullable|string|max:60',
            'doors'           => 'nullable|int',
            'seats'           => 'nullable|int',
            'location'        => 'nullable|string|max:120',
            'title_en'        => 'required|string|min:3|max:220',
        ];
    }
}

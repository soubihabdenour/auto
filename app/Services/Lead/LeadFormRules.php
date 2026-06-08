<?php

declare(strict_types=1);

namespace App\Services\Lead;

/**
 * Validation rule sets for the three public lead-capture forms.
 * Keeping them in one place lets the controller stay thin and ensures
 * front-end / back-end stay in sync.
 */
final class LeadFormRules
{
    /** @return array<string, string> */
    public static function vehicleInquiry(): array
    {
        return [
            'name'       => 'required|string|min:2|max:150',
            'phone'      => 'required|phone',
            'whatsapp'   => 'nullable|phone',
            'city'       => 'nullable|string|max:120',
            'message'    => 'nullable|string|max:2000',
            'vehicle_id' => 'nullable|int',
            'lead_type'  => 'required|in:inquiry,quotation,reservation',
        ];
    }

    /** @return array<string, string> */
    public static function requestVehicle(): array
    {
        return [
            'name'         => 'required|string|min:2|max:150',
            'phone'        => 'required|phone',
            'whatsapp'     => 'nullable|phone',
            'city'         => 'nullable|string|max:120',
            'brand'        => 'nullable|string|max:80',
            'year_min'     => 'nullable|int',
            'year_max'     => 'nullable|int',
            'budget_usd'   => 'nullable|numeric',
            'fuel'         => 'nullable|in:petrol,diesel,hybrid,phev,electric,lpg',
            'transmission' => 'nullable|in:manual,automatic,dct,cvt',
            'notes'        => 'nullable|string|max:2000',
        ];
    }

    /** @return array<string, string> */
    public static function contact(): array
    {
        return [
            'name'     => 'required|string|min:2|max:150',
            'phone'    => 'required|phone',
            'whatsapp' => 'nullable|phone',
            'email'    => 'nullable|email|max:190',
            'message'  => 'required|string|min:5|max:2000',
        ];
    }
}

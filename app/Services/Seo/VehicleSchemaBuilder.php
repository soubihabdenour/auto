<?php

declare(strict_types=1);

namespace App\Services\Seo;

/**
 * Builds JSON-LD schema.org Vehicle markup for the detail page.
 * Output is encoded by caller via json_encode.
 */
final class VehicleSchemaBuilder
{
    public function __construct(private string $siteName, private string $siteUrl) {}

    /** @param array<string,mixed> $vehicle  @param array<int,array<string,mixed>> $images */
    public function build(array $vehicle, array $images, string $canonicalUrl): array
    {
        $imageUrls = array_values(array_filter(array_map(
            fn ($img) => isset($img['path']) ? rtrim($this->siteUrl, '/') . '/uploads/' . ltrim((string) $img['path'], '/') : null,
            $images
        )));

        return [
            '@context'       => 'https://schema.org',
            '@type'          => 'Vehicle',
            'name'           => (string) ($vehicle['title'] ?? ''),
            'description'    => (string) ($vehicle['description'] ?? $vehicle['meta_description'] ?? ''),
            'brand'          => ['@type' => 'Brand', 'name' => (string) ($vehicle['brand_name'] ?? '')],
            'model'          => (string) ($vehicle['model_name'] ?? ''),
            'vehicleModelDate' => (int) ($vehicle['year'] ?? 0),
            'mileageFromOdometer' => [
                '@type'    => 'QuantitativeValue',
                'value'    => (int) ($vehicle['mileage_km'] ?? 0),
                'unitCode' => 'KMT',
            ],
            'fuelType'              => (string) ($vehicle['fuel_type'] ?? ''),
            'vehicleTransmission'   => (string) ($vehicle['transmission'] ?? ''),
            'driveWheelConfiguration'=> (string) ($vehicle['drivetrain'] ?? ''),
            'color'                 => (string) ($vehicle['exterior_color'] ?? ''),
            'vehicleIdentificationNumber' => (string) ($vehicle['vin'] ?? ''),
            'image'  => $imageUrls,
            'offers' => [
                '@type'         => 'Offer',
                'priceCurrency' => 'USD',
                'price'         => (float) ($vehicle['price_usd'] ?? 0),
                'availability'  => match ((string) ($vehicle['status'] ?? '')) {
                    'available' => 'https://schema.org/InStock',
                    'reserved'  => 'https://schema.org/PreOrder',
                    'sold'      => 'https://schema.org/SoldOut',
                    default     => 'https://schema.org/InStock',
                },
                'url'           => $canonicalUrl,
                'seller'        => ['@type' => 'Organization', 'name' => $this->siteName, 'url' => $this->siteUrl],
            ],
            'url' => $canonicalUrl,
        ];
    }
}

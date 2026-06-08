<?php

declare(strict_types=1);

namespace App\Services\Vehicle;

use App\Repositories\BrandRepository;
use App\Repositories\ModelRepository;
use App\Repositories\VehicleRepository;
use App\Services\SlugGenerator;

/**
 * Translates the admin vehicle form payload into repository writes:
 *   - vehicle row (with a unique slug derived from brand/model/year/fuel/vin)
 *   - per-locale translations
 *   - optional inspection record
 */
final class VehicleAdminWriter
{
    public function __construct(
        private VehicleRepository $vehicles,
        private BrandRepository   $brands,
        private ModelRepository   $models,
    ) {}

    /**
     * @param  array<string, mixed> $post     Validated POST payload
     * @param  array<string, mixed>|null $existing  Current DB row, or null when creating
     * @return array<string, mixed>          Row ready for VehicleRepository::create/update
     */
    public function buildDataPayload(array $post, ?array $existing, ?int $createdByUserId): array
    {
        $brandId = (int) $post['brand_id'];
        $modelId = (int) $post['model_id'];

        $brandName = $this->lookupName($this->brands->allActive(), $brandId);
        $modelName = $this->lookupName($this->models->byBrand($brandId), $modelId);

        $slug = $existing['slug'] ?? SlugGenerator::forVehicle(
            (int) $post['year'],
            $brandName,
            $modelName,
            (string) $post['fuel_type'],
            $post['vin'] ?? null,
        );

        // Ensure uniqueness — append -2, -3, … up to -20
        $original = $slug;
        $i = 2;
        while ($this->vehicles->existsSlug($slug, $existing['id'] ?? null)) {
            $slug = $original . '-' . $i++;
            if ($i > 20) break;
        }

        return [
            'slug'            => $slug,
            'brand_id'        => $brandId,
            'model_id'        => $modelId,
            'body_type_id'    => empty($post['body_type_id']) ? null : (int) $post['body_type_id'],
            'year'            => (int) $post['year'],
            'vin'             => $post['vin'] ?: null,
            'mileage_km'      => (int) $post['mileage_km'],
            'engine_cc'       => empty($post['engine_cc']) ? null : (int) $post['engine_cc'],
            'engine_power_hp' => empty($post['engine_power_hp']) ? null : (int) $post['engine_power_hp'],
            'transmission'    => (string) $post['transmission'],
            'fuel_type'       => (string) $post['fuel_type'],
            'drivetrain'      => $post['drivetrain'] ?: 'fwd',
            'exterior_color'  => $post['exterior_color'] ?: null,
            'interior_color'  => $post['interior_color'] ?: null,
            'doors'           => empty($post['doors']) ? null : (int) $post['doors'],
            'seats'           => empty($post['seats']) ? null : (int) $post['seats'],
            'origin_country'  => $post['origin_country'] ?: 'South Korea',
            'location'        => $post['location'] ?: null,
            'price_usd'       => (float) $post['price_usd'],
            'price_currency'  => 'USD',
            'listing_type'    => 'sale',
            'status'          => (string) $post['status'],
            'is_featured'     => ! empty($post['is_featured']) ? 1 : 0,
            'published_at'    => ($post['status'] === 'available' && empty($existing['published_at']))
                                    ? date('Y-m-d H:i:s')
                                    : ($existing['published_at'] ?? null),
            'created_by'      => $existing['created_by'] ?? $createdByUserId,
        ];
    }

    /** @param array<string, mixed> $post */
    public function upsertTranslations(int $vehicleId, array $post): void
    {
        foreach ((array) config('locales.available', ['ar', 'fr', 'en']) as $loc) {
            $this->vehicles->upsertTranslation($vehicleId, $loc, [
                'title'            => trim((string) ($post['title_' . $loc] ?? '')),
                'description'      => $post['description_' . $loc] ?? null,
                'meta_title'       => $post['meta_title_' . $loc] ?? null,
                'meta_description' => $post['meta_description_' . $loc] ?? null,
            ]);
        }
    }

    /** @param array<string, mixed> $post */
    public function maybeUpsertInspection(int $vehicleId, array $post): void
    {
        // Only write if at least overall_score is present
        if (empty($post['overall_score'])) {
            return;
        }
        $this->vehicles->upsertInspection($vehicleId, [
            'overall_score'    => (int) $post['overall_score'],
            'engine_score'     => (int) ($post['engine_score']     ?? 0) ?: null,
            'exterior_score'   => (int) ($post['exterior_score']   ?? 0) ?: null,
            'interior_score'   => (int) ($post['interior_score']   ?? 0) ?: null,
            'tires_score'      => (int) ($post['tires_score']      ?? 0) ?: null,
            'brakes_score'     => (int) ($post['brakes_score']     ?? 0) ?: null,
            'electrical_score' => (int) ($post['electrical_score'] ?? 0) ?: null,
            'accident_history' => in_array($post['accident_history'] ?? '', VehicleFormRules::ACCIDENT, true)
                                     ? $post['accident_history'] : 'unknown',
            'inspector_name'   => $post['inspector_name'] ?: null,
            'inspected_at'     => $post['inspected_at'] ?: null,
            'notes_ar'         => $post['notes_ar'] ?: null,
            'notes_fr'         => $post['notes_fr'] ?: null,
            'notes_en'         => $post['notes_en'] ?: null,
        ]);
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function lookupName(array $rows, int $id): string
    {
        foreach ($rows as $row) {
            if ((int) $row['id'] === $id) {
                return (string) $row['name'];
            }
        }
        return '';
    }
}

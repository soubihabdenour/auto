<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Immutable DTO describing a vehicle search request. Built from a request's
 * query string and passed into VehicleRepository::search().
 */
final class VehicleSearchCriteria
{
    public function __construct(
        public readonly ?int    $brandId       = null,
        public readonly ?int    $modelId       = null,
        public readonly ?int    $bodyTypeId    = null,
        public readonly ?int    $yearMin       = null,
        public readonly ?int    $yearMax       = null,
        public readonly ?float  $priceMinUsd   = null,
        public readonly ?float  $priceMaxUsd   = null,
        public readonly ?int    $mileageMax    = null,
        public readonly ?string $fuelType      = null,   // single value
        public readonly ?string $transmission  = null,
        public readonly ?string $search        = null,   // free-text on title/desc
        public readonly string  $sort          = 'newest', // newest|price_asc|price_desc|mileage_asc|year_desc
        public readonly int     $page          = 1,
        public readonly int     $perPage       = 12,
    ) {}

    public static function fromArray(array $q): self
    {
        $toInt = fn ($v) => ($v === null || $v === '') ? null : (int) $v;
        $toFlt = fn ($v) => ($v === null || $v === '') ? null : (float) $v;
        $toStr = fn ($v) => ($v === null || $v === '') ? null : trim((string) $v);

        $sort = $q['sort'] ?? 'newest';
        if (! in_array($sort, ['newest', 'price_asc', 'price_desc', 'mileage_asc', 'year_desc'], true)) {
            $sort = 'newest';
        }

        $perPage = max(1, min(48, (int) ($q['per_page'] ?? 12)));

        return new self(
            brandId:      $toInt($q['brand_id']      ?? null),
            modelId:      $toInt($q['model_id']      ?? null),
            bodyTypeId:   $toInt($q['body_type_id']  ?? null),
            yearMin:      $toInt($q['year_min']      ?? null),
            yearMax:      $toInt($q['year_max']      ?? null),
            priceMinUsd:  $toFlt($q['price_min']     ?? null),
            priceMaxUsd:  $toFlt($q['price_max']     ?? null),
            mileageMax:   $toInt($q['mileage_max']   ?? null),
            fuelType:     $toStr($q['fuel']          ?? null),
            transmission: $toStr($q['transmission']  ?? null),
            search:       $toStr($q['q']             ?? null),
            sort:         (string) $sort,
            page:         max(1, (int) ($q['page'] ?? 1)),
            perPage:      $perPage,
        );
    }

    /** Convert back to a query string for pagination/sort links. */
    public function toQueryArray(): array
    {
        $out = [];
        foreach ([
            'brand_id' => $this->brandId,
            'model_id' => $this->modelId,
            'body_type_id' => $this->bodyTypeId,
            'year_min' => $this->yearMin,
            'year_max' => $this->yearMax,
            'price_min' => $this->priceMinUsd,
            'price_max' => $this->priceMaxUsd,
            'mileage_max' => $this->mileageMax,
            'fuel' => $this->fuelType,
            'transmission' => $this->transmission,
            'q' => $this->search,
            'sort' => $this->sort === 'newest' ? null : $this->sort,
            'per_page' => $this->perPage === 12 ? null : $this->perPage,
            'page' => $this->page === 1 ? null : $this->page,
        ] as $k => $v) {
            if ($v !== null && $v !== '') {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}

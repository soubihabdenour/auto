<?php

declare(strict_types=1);

namespace App\Repositories;

final class ModelRepository extends BaseRepository
{
    /** @return array<int, array<string, mixed>> */
    public function allActive(): array
    {
        return $this->db->select(
            'SELECT id, brand_id, slug, name
               FROM models
              WHERE is_active = 1
              ORDER BY brand_id, sort_order, name'
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function byBrand(int $brandId): array
    {
        return $this->db->select(
            'SELECT id, slug, name
               FROM models
              WHERE is_active = 1 AND brand_id = ?
              ORDER BY sort_order, name',
            [$brandId]
        );
    }
}

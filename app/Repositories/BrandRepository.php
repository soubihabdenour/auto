<?php

declare(strict_types=1);

namespace App\Repositories;

final class BrandRepository extends BaseRepository
{
    /** @return array<int, array<string, mixed>> */
    public function allActive(): array
    {
        return $this->db->select(
            'SELECT id, slug, name, logo_path
               FROM brands
              WHERE is_active = 1
              ORDER BY sort_order, name'
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->selectOne(
            'SELECT * FROM brands WHERE slug = ? LIMIT 1',
            [$slug]
        );
    }
}

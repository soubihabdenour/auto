<?php

declare(strict_types=1);

namespace App\Repositories;

final class BodyTypeRepository extends BaseRepository
{
    /** @return array<int, array<string, mixed>> */
    public function all(string $locale = 'ar'): array
    {
        $col = match ($locale) {
            'fr' => 'name_fr',
            'en' => 'name_en',
            default => 'name_ar',
        };
        return $this->db->select(
            "SELECT id, `key`, {$col} AS name, icon_path, sort_order
               FROM body_types
              ORDER BY sort_order, {$col}"
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

final class VehicleRepository extends BaseRepository
{
    /**
     * Reusable SELECT body that joins everything needed for a vehicle card.
     */
    private function listSelect(string $locale): string
    {
        return "
            SELECT
                v.id, v.slug, v.year, v.mileage_km, v.fuel_type, v.transmission,
                v.exterior_color, v.location, v.price_usd, v.is_featured, v.status,
                v.brand_id, v.model_id, v.body_type_id, v.cover_image_id,
                v.published_at, v.created_at,
                b.name AS brand_name, b.slug AS brand_slug,
                m.name AS model_name, m.slug AS model_slug,
                bt.`key` AS body_type_key,
                COALESCE(vt.title, CONCAT(b.name, ' ', m.name, ' ', v.year))       AS title,
                COALESCE(vt.meta_title, CONCAT(b.name, ' ', m.name, ' ', v.year))  AS meta_title,
                vt.meta_description,
                ci.path AS cover_image_path
            FROM vehicles v
            INNER JOIN brands b ON b.id = v.brand_id
            INNER JOIN models m ON m.id = v.model_id
            LEFT  JOIN body_types bt ON bt.id = v.body_type_id
            LEFT  JOIN vehicle_translations vt ON vt.vehicle_id = v.id AND vt.locale = :locale
            LEFT  JOIN vehicle_images ci ON ci.id = v.cover_image_id
        ";
    }

    /** @return array<int, array<string, mixed>> */
    public function findFeatured(int $limit, string $locale): array
    {
        $sql = $this->listSelect($locale) . "
            WHERE v.status = 'available' AND v.is_featured = 1
            ORDER BY v.published_at DESC, v.id DESC
            LIMIT :lim
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->bindValue(':locale', $locale);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function findLatest(int $limit, string $locale): array
    {
        $sql = $this->listSelect($locale) . "
            WHERE v.status = 'available'
            ORDER BY v.published_at DESC, v.id DESC
            LIMIT :lim
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->bindValue(':locale', $locale);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array{0: array<int, array<string,mixed>>, 1: int}  rows, total
     */
    public function search(VehicleSearchCriteria $c, string $locale): array
    {
        [$where, $params] = $this->buildWhere($c);
        $params[':locale'] = $locale;

        $orderBy = match ($c->sort) {
            'price_asc'   => 'v.price_usd ASC',
            'price_desc'  => 'v.price_usd DESC',
            'mileage_asc' => 'v.mileage_km ASC',
            'year_desc'   => 'v.year DESC, v.published_at DESC',
            default       => 'v.published_at DESC, v.id DESC',
        };

        $offset = ($c->page - 1) * $c->perPage;

        $sql = $this->listSelect($locale) . "
            {$where}
            ORDER BY {$orderBy}
            LIMIT :lim OFFSET :off
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, $this->paramType($v));
        }
        $stmt->bindValue(':lim', $c->perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,     \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $total = $this->count($c, $locale);

        return [$rows, $total];
    }

    public function count(VehicleSearchCriteria $c, string $locale = 'ar'): int
    {
        [$where, $params] = $this->buildWhere($c);
        $params[':locale'] = $locale;

        $sql = "
            SELECT COUNT(*) AS cnt
              FROM vehicles v
              INNER JOIN brands b ON b.id = v.brand_id
              INNER JOIN models m ON m.id = v.model_id
              LEFT  JOIN vehicle_translations vt ON vt.vehicle_id = v.id AND vt.locale = :locale
            {$where}
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, $this->paramType($v));
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(VehicleSearchCriteria $c): array
    {
        $w = ["v.status = 'available'"];
        $p = [];

        if ($c->brandId)      { $w[] = 'v.brand_id = :brand_id';            $p[':brand_id'] = $c->brandId; }
        if ($c->modelId)      { $w[] = 'v.model_id = :model_id';            $p[':model_id'] = $c->modelId; }
        if ($c->bodyTypeId)   { $w[] = 'v.body_type_id = :body_type_id';    $p[':body_type_id'] = $c->bodyTypeId; }
        if ($c->yearMin)      { $w[] = 'v.year >= :year_min';               $p[':year_min'] = $c->yearMin; }
        if ($c->yearMax)      { $w[] = 'v.year <= :year_max';               $p[':year_max'] = $c->yearMax; }
        if ($c->priceMinUsd)  { $w[] = 'v.price_usd >= :price_min';         $p[':price_min'] = $c->priceMinUsd; }
        if ($c->priceMaxUsd)  { $w[] = 'v.price_usd <= :price_max';         $p[':price_max'] = $c->priceMaxUsd; }
        if ($c->mileageMax)   { $w[] = 'v.mileage_km <= :mileage_max';      $p[':mileage_max'] = $c->mileageMax; }
        if ($c->fuelType)     { $w[] = 'v.fuel_type = :fuel';               $p[':fuel'] = $c->fuelType; }
        if ($c->transmission) { $w[] = 'v.transmission = :transmission';    $p[':transmission'] = $c->transmission; }
        if ($c->search) {
            $w[] = '(vt.title LIKE :q OR vt.description LIKE :q OR b.name LIKE :q OR m.name LIKE :q)';
            $p[':q'] = '%' . $c->search . '%';
        }

        return ['WHERE ' . implode(' AND ', $w), $p];
    }

    private function paramType(mixed $v): int
    {
        if (is_int($v))  return \PDO::PARAM_INT;
        if (is_bool($v)) return \PDO::PARAM_BOOL;
        if ($v === null) return \PDO::PARAM_NULL;
        return \PDO::PARAM_STR;
    }

    public function findBySlug(string $slug, string $locale): ?array
    {
        $sql = $this->listSelect($locale) . "
            WHERE v.slug = :slug AND v.status IN ('available','reserved','sold')
            LIMIT 1
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->bindValue(':locale', $locale);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        // Enrich with full description and remaining vehicle columns
        $extra = $this->db->selectOne(
            'SELECT v.vin, v.engine_cc, v.engine_power_hp, v.drivetrain,
                    v.interior_color, v.doors, v.seats, v.origin_country,
                    vt.description
               FROM vehicles v
               LEFT JOIN vehicle_translations vt ON vt.vehicle_id = v.id AND vt.locale = ?
              WHERE v.id = ?',
            [$locale, (int) $row['id']]
        );
        return $extra ? array_merge($row, $extra) : $row;
    }

    /** @return array<int, array<string, mixed>> */
    public function imagesFor(int $vehicleId): array
    {
        return $this->db->select(
            'SELECT id, path, alt_ar, alt_fr, alt_en, width, height, is_cover, sort_order
               FROM vehicle_images
              WHERE vehicle_id = ?
              ORDER BY is_cover DESC, sort_order, id',
            [$vehicleId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function videosFor(int $vehicleId): array
    {
        return $this->db->select(
            'SELECT id, provider, path, external_url, poster_path, duration_sec, sort_order
               FROM vehicle_videos
              WHERE vehicle_id = ?
              ORDER BY sort_order, id',
            [$vehicleId]
        );
    }

    public function inspectionFor(int $vehicleId): ?array
    {
        return $this->db->selectOne(
            'SELECT *
               FROM inspection_reports
              WHERE vehicle_id = ?',
            [$vehicleId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function findSimilar(int $vehicleId, int $brandId, int $bodyTypeId, string $locale, int $limit = 4): array
    {
        $sql = $this->listSelect($locale) . "
            WHERE v.status = 'available' AND v.id != :id
              AND (v.brand_id = :brand_id OR v.body_type_id = :body_type_id)
            ORDER BY (v.brand_id = :brand_id) DESC, v.published_at DESC
            LIMIT :lim
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->bindValue(':locale', $locale);
        $stmt->bindValue(':id', $vehicleId, \PDO::PARAM_INT);
        $stmt->bindValue(':brand_id', $brandId, \PDO::PARAM_INT);
        $stmt->bindValue(':body_type_id', $bodyTypeId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function incrementViews(int $vehicleId): void
    {
        $this->db->execute(
            'UPDATE vehicles SET views_count = views_count + 1 WHERE id = ?',
            [$vehicleId]
        );
    }

    // ---------- Admin / stats ----------

    /** @return array<string, int>  status → count */
    public function countsByStatus(): array
    {
        $rows = $this->db->select(
            'SELECT status, COUNT(*) AS n FROM vehicles GROUP BY status'
        );
        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r['status']] = (int) $r['n'];
        }
        return $out;
    }

    /** @return array<int, array<string,mixed>> */
    public function findMostViewed(int $limit, string $locale): array
    {
        $sql = $this->listSelect($locale) . "
            WHERE v.status = 'available'
            ORDER BY v.views_count DESC, v.published_at DESC
            LIMIT :lim
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->bindValue(':locale', $locale);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public function adminList(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminFilters($filters);
        $sql = "
            SELECT v.id, v.slug, v.year, v.mileage_km, v.fuel_type, v.transmission,
                   v.price_usd, v.status, v.is_featured, v.views_count, v.created_at,
                   b.name AS brand_name, m.name AS model_name
              FROM vehicles v
              JOIN brands b ON b.id = v.brand_id
              JOIN models m ON m.id = v.model_id
            {$where}
             ORDER BY v.created_at DESC, v.id DESC
             LIMIT :lim OFFSET :off
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function adminCount(array $filters): int
    {
        [$where, $params] = $this->adminFilters($filters);
        $sql = "SELECT COUNT(*) AS n FROM vehicles v {$where}";
        $stmt = $this->db->pdo()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['n'] ?? 0);
    }

    /** @return array{0:string,1:array<string,mixed>} */
    private function adminFilters(array $f): array
    {
        $w = []; $p = [];
        if (! empty($f['status']))   { $w[] = 'v.status = :status';      $p[':status']   = $f['status']; }
        if (! empty($f['brand_id'])) { $w[] = 'v.brand_id = :brand_id';  $p[':brand_id'] = (int) $f['brand_id']; }
        if (! empty($f['featured'])) { $w[] = 'v.is_featured = 1'; }
        if (! empty($f['q'])) {
            $w[] = '(v.slug LIKE :q OR v.vin LIKE :q)';
            $p[':q'] = '%' . $f['q'] . '%';
        }
        $where = empty($w) ? '' : 'WHERE ' . implode(' AND ', $w);
        return [$where, $p];
    }

    public function findRawById(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM vehicles WHERE id = ?', [$id]);
    }

    /** @return array<int, array<string,mixed>>  locale → translation row */
    public function translationsFor(int $vehicleId): array
    {
        return $this->db->select(
            'SELECT locale, title, description, meta_title, meta_description
               FROM vehicle_translations
              WHERE vehicle_id = ?',
            [$vehicleId]
        );
    }

    public function existsSlug(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT id FROM vehicles WHERE slug = ?';
        $params = [$slug];
        if ($exceptId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }
        return $this->db->selectOne($sql . ' LIMIT 1', $params) !== null;
    }

    // ---------- Admin / writes ----------

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        $cols = [
            'slug','brand_id','model_id','body_type_id','year','vin','mileage_km',
            'engine_cc','engine_power_hp','transmission','fuel_type','drivetrain',
            'exterior_color','interior_color','doors','seats','origin_country','location',
            'price_usd','price_currency','listing_type','status','is_featured',
            'published_at','created_by',
        ];
        $values = [];
        $placeholders = [];
        foreach ($cols as $c) {
            $placeholders[] = '?';
            $values[]       = $data[$c] ?? null;
        }
        $this->db->execute(
            'INSERT INTO vehicles (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')',
            $values
        );
        return (int) $this->db->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): void
    {
        $sets = [];
        $values = [];
        foreach ($data as $col => $val) {
            $sets[]   = "`{$col}` = ?";
            $values[] = $val;
        }
        if (empty($sets)) return;
        $values[] = $id;
        $this->db->execute(
            'UPDATE vehicles SET ' . implode(', ', $sets) . ' WHERE id = ?',
            $values
        );
    }

    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM vehicles WHERE id = ?', [$id]);
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db->execute('UPDATE vehicles SET status = ? WHERE id = ?', [$status, $id]);
    }

    /** @param array<string,mixed> $tr */
    public function upsertTranslation(int $vehicleId, string $locale, array $tr): void
    {
        $existing = $this->db->selectOne(
            'SELECT id FROM vehicle_translations WHERE vehicle_id = ? AND locale = ?',
            [$vehicleId, $locale]
        );
        if ($existing) {
            $this->db->execute(
                'UPDATE vehicle_translations
                    SET title = ?, description = ?, meta_title = ?, meta_description = ?
                  WHERE id = ?',
                [$tr['title'] ?? '', $tr['description'] ?? null,
                 $tr['meta_title'] ?? null, $tr['meta_description'] ?? null,
                 (int) $existing['id']]
            );
            return;
        }
        $this->db->execute(
            'INSERT INTO vehicle_translations
                (vehicle_id, locale, title, description, meta_title, meta_description)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$vehicleId, $locale,
             $tr['title'] ?? '', $tr['description'] ?? null,
             $tr['meta_title'] ?? null, $tr['meta_description'] ?? null]
        );
    }

    /** @param array<string,mixed> $i */
    public function upsertInspection(int $vehicleId, array $i): void
    {
        $existing = $this->db->selectOne(
            'SELECT id FROM inspection_reports WHERE vehicle_id = ?',
            [$vehicleId]
        );
        $cols = [
            'overall_score','engine_score','exterior_score','interior_score',
            'tires_score','brakes_score','electrical_score','accident_history',
            'inspector_name','inspected_at','notes_ar','notes_fr','notes_en',
        ];
        $values = array_map(fn($c) => $i[$c] ?? null, $cols);

        if ($existing) {
            $sets = implode(', ', array_map(fn($c) => "`{$c}` = ?", $cols));
            $values[] = (int) $existing['id'];
            $this->db->execute(
                "UPDATE inspection_reports SET {$sets} WHERE id = ?",
                $values
            );
            return;
        }
        $colList = implode(',', $cols);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        array_unshift($values, $vehicleId);
        $this->db->execute(
            "INSERT INTO inspection_reports (vehicle_id, {$colList}) VALUES (?, {$placeholders})",
            $values
        );
    }

    // ---------- Image management ----------

    /** @param array<string,mixed> $data */
    public function addImage(int $vehicleId, array $data): int
    {
        $this->db->execute(
            'INSERT INTO vehicle_images
                (vehicle_id, path, alt_ar, alt_fr, alt_en, width, height, size_bytes, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $vehicleId,
                $data['path'] ?? '',
                $data['alt_ar'] ?? null, $data['alt_fr'] ?? null, $data['alt_en'] ?? null,
                $data['width'] ?? null, $data['height'] ?? null, $data['size_bytes'] ?? null,
                $data['sort_order'] ?? 0,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function setCoverImage(int $vehicleId, int $imageId): void
    {
        $this->db->execute('UPDATE vehicle_images SET is_cover = 0 WHERE vehicle_id = ?', [$vehicleId]);
        $this->db->execute('UPDATE vehicle_images SET is_cover = 1 WHERE id = ? AND vehicle_id = ?', [$imageId, $vehicleId]);
        $this->db->execute('UPDATE vehicles SET cover_image_id = ? WHERE id = ?', [$imageId, $vehicleId]);
    }

    public function deleteImage(int $imageId): ?array
    {
        $row = $this->db->selectOne('SELECT id, vehicle_id, path, is_cover FROM vehicle_images WHERE id = ?', [$imageId]);
        if ($row === null) return null;
        $this->db->execute('DELETE FROM vehicle_images WHERE id = ?', [$imageId]);
        if ((int) $row['is_cover'] === 1) {
            // clear cover_image_id on the vehicle
            $this->db->execute('UPDATE vehicles SET cover_image_id = NULL WHERE id = ?', [(int) $row['vehicle_id']]);
        }
        return $row;
    }
}

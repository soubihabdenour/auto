<?php

declare(strict_types=1);

namespace App\Repositories;

final class TestimonialRepository extends BaseRepository
{
    /** @return array<int, array<string, mixed>> */
    public function adminAll(): array
    {
        return $this->db->select(
            'SELECT t.id, t.customer_name, t.customer_city, t.rating,
                    t.vehicle_purchased, t.is_published, t.sort_order, t.created_at
               FROM testimonials t
              ORDER BY t.sort_order, t.id DESC'
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM testimonials WHERE id = ?', [$id]);
    }

    /** @return array<string, string>  locale → body */
    public function translationsFor(int $id): array
    {
        $rows = $this->db->select(
            'SELECT locale, body FROM testimonial_translations WHERE testimonial_id = ?',
            [$id]
        );
        $out = [];
        foreach ($rows as $r) $out[(string) $r['locale']] = (string) $r['body'];
        return $out;
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO testimonials
                (customer_name, customer_city, avatar_path, rating, vehicle_purchased, is_published, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['customer_name'] ?? '',
                $data['customer_city'] ?? null,
                $data['avatar_path']  ?? null,
                (int) ($data['rating'] ?? 5),
                $data['vehicle_purchased'] ?? null,
                ! empty($data['is_published']) ? 1 : 0,
                (int) ($data['sort_order'] ?? 0),
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->db->execute(
            'UPDATE testimonials
                SET customer_name = ?, customer_city = ?, rating = ?,
                    vehicle_purchased = ?, is_published = ?, sort_order = ?
              WHERE id = ?',
            [
                $data['customer_name'] ?? '',
                $data['customer_city'] ?? null,
                (int) ($data['rating'] ?? 5),
                $data['vehicle_purchased'] ?? null,
                ! empty($data['is_published']) ? 1 : 0,
                (int) ($data['sort_order'] ?? 0),
                $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM testimonials WHERE id = ?', [$id]);
    }

    public function upsertTranslation(int $id, string $locale, string $body): void
    {
        $existing = $this->db->selectOne(
            'SELECT id FROM testimonial_translations WHERE testimonial_id = ? AND locale = ?',
            [$id, $locale]
        );
        if ($existing) {
            $this->db->execute(
                'UPDATE testimonial_translations SET body = ? WHERE id = ?',
                [$body, (int) $existing['id']]
            );
            return;
        }
        $this->db->execute(
            'INSERT INTO testimonial_translations (testimonial_id, locale, body) VALUES (?, ?, ?)',
            [$id, $locale, $body]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function published(string $locale, int $limit = 12): array
    {
        $sql = "
            SELECT t.id, t.customer_name, t.customer_city, t.avatar_path,
                   t.rating, t.vehicle_purchased, t.sort_order,
                   COALESCE(tr.body, tr_fb.body) AS body
              FROM testimonials t
              LEFT JOIN testimonial_translations tr    ON tr.testimonial_id = t.id    AND tr.locale = :locale
              LEFT JOIN testimonial_translations tr_fb ON tr_fb.testimonial_id = t.id AND tr_fb.locale = 'en'
             WHERE t.is_published = 1
             ORDER BY t.sort_order, t.id DESC
             LIMIT :lim
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->bindValue(':locale', $locale);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

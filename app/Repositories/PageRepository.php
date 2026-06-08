<?php

declare(strict_types=1);

namespace App\Repositories;

final class PageRepository extends BaseRepository
{
    public function findByKey(string $key, string $locale, string $fallback = 'en'): ?array
    {
        $row = $this->db->selectOne(
            'SELECT p.id, p.`key`, p.template, p.is_published,
                    pt.title, pt.body, pt.meta_title, pt.meta_description
               FROM pages p
               LEFT JOIN page_translations pt ON pt.page_id = p.id AND pt.locale = ?
              WHERE p.`key` = ? AND p.is_published = 1
              LIMIT 1',
            [$locale, $key]
        );
        if ($row === null) {
            return null;
        }
        // Fall back if translation missing
        if ($row['title'] === null && $locale !== $fallback) {
            $fbRow = $this->db->selectOne(
                'SELECT title, body, meta_title, meta_description
                   FROM page_translations
                  WHERE page_id = ? AND locale = ?',
                [(int) $row['id'], $fallback]
            );
            if ($fbRow) {
                $row['title']            = $fbRow['title'];
                $row['body']             = $fbRow['body'];
                $row['meta_title']       = $fbRow['meta_title'];
                $row['meta_description'] = $fbRow['meta_description'];
            }
        }
        return $row;
    }
}

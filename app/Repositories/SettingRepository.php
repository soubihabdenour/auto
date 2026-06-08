<?php

declare(strict_types=1);

namespace App\Repositories;

final class SettingRepository extends BaseRepository
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return $this->db->select('SELECT `key`, `value`, `type` FROM settings');
    }

    public function get(string $key): ?array
    {
        return $this->db->selectOne('SELECT `value`, `type` FROM settings WHERE `key` = ?', [$key]);
    }

    public function set(string $key, mixed $value): void
    {
        // String-coerce for storage; type column stays whatever it was.
        $serial = is_array($value) || is_object($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string) $value;
        $this->db->execute(
            'UPDATE settings SET `value` = ? WHERE `key` = ?',
            [$serial, $key]
        );
    }
}

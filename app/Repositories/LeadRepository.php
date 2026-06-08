<?php

declare(strict_types=1);

namespace App\Repositories;

final class LeadRepository extends BaseRepository
{
    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $cols = [
            'vehicle_id', 'name', 'phone', 'whatsapp', 'email', 'country', 'city',
            'message', 'lead_type', 'status', 'source', 'locale', 'ip_hash',
            'user_agent', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign',
        ];
        $values = [];
        $placeholders = [];
        foreach ($cols as $col) {
            $placeholders[] = '?';
            $values[]       = $data[$col] ?? null;
        }
        $sql = 'INSERT INTO leads (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
        $this->db->execute($sql, $values);
        return (int) $this->db->lastInsertId();
    }

    public function logWhatsappClick(?int $vehicleId, string $locale, string $ipHash, string $uaHash): void
    {
        $this->db->execute(
            'INSERT INTO whatsapp_click_events (vehicle_id, locale, ip_hash, user_agent_hash) VALUES (?, ?, ?, ?)',
            [$vehicleId, $locale, $ipHash, $uaHash]
        );
    }

    // ---------- Admin / stats ----------

    public function countTotal(): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS n FROM leads');
        return (int) ($row['n'] ?? 0);
    }

    public function countByStatus(string $status): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS n FROM leads WHERE status = ?', [$status]);
        return (int) ($row['n'] ?? 0);
    }

    public function countSince(string $datetime): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS n FROM leads WHERE created_at >= ?', [$datetime]);
        return (int) ($row['n'] ?? 0);
    }

    /** @return array<int, array<string,mixed>> */
    public function recent(int $limit): array
    {
        $stmt = $this->db->pdo()->prepare(
            "SELECT l.id, l.name, l.phone, l.lead_type, l.status, l.created_at,
                    l.vehicle_id,
                    CASE WHEN v.id IS NULL
                         THEN NULL
                         ELSE CONCAT(b.name, ' ', m.name, ' ', v.year) END AS vehicle_label
               FROM leads l
               LEFT JOIN vehicles v ON v.id = l.vehicle_id
               LEFT JOIN brands b   ON b.id = v.brand_id
               LEFT JOIN models m   ON m.id = v.model_id
              ORDER BY l.created_at DESC, l.id DESC
              LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public function adminList(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminFilters($filters);
        $sql = "
            SELECT l.id, l.name, l.phone, l.lead_type, l.status, l.source, l.created_at,
                   l.vehicle_id, l.assigned_to,
                   CASE WHEN v.id IS NULL
                        THEN NULL
                        ELSE CONCAT(b.name, ' ', m.name, ' ', v.year) END AS vehicle_label,
                   u.name AS assignee_name
              FROM leads l
              LEFT JOIN vehicles v ON v.id = l.vehicle_id
              LEFT JOIN brands b   ON b.id = v.brand_id
              LEFT JOIN models m   ON m.id = v.model_id
              LEFT JOIN users u    ON u.id = l.assigned_to
            {$where}
             ORDER BY l.created_at DESC, l.id DESC
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
        $stmt = $this->db->pdo()->prepare("SELECT COUNT(*) AS n FROM leads l {$where}");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['n'] ?? 0);
    }

    /** @return array{0:string,1:array<string,mixed>} */
    private function adminFilters(array $f): array
    {
        $w = []; $p = [];
        if (! empty($f['status']))    { $w[] = 'l.status = :status';      $p[':status'] = $f['status']; }
        if (! empty($f['lead_type'])) { $w[] = 'l.lead_type = :type';     $p[':type']   = $f['lead_type']; }
        if (! empty($f['source']))    { $w[] = 'l.source = :source';      $p[':source'] = $f['source']; }
        if (! empty($f['q'])) {
            $w[] = '(l.name LIKE :q OR l.phone LIKE :q OR l.email LIKE :q)';
            $p[':q'] = '%' . $f['q'] . '%';
        }
        $where = empty($w) ? '' : 'WHERE ' . implode(' AND ', $w);
        return [$where, $p];
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne(
            'SELECT l.*, CASE WHEN v.id IS NULL THEN NULL
                              ELSE CONCAT(b.name, " ", m.name, " ", v.year) END AS vehicle_label,
                    v.slug AS vehicle_slug, u.name AS assignee_name
               FROM leads l
               LEFT JOIN vehicles v ON v.id = l.vehicle_id
               LEFT JOIN brands b   ON b.id = v.brand_id
               LEFT JOIN models m   ON m.id = v.model_id
               LEFT JOIN users u    ON u.id = l.assigned_to
              WHERE l.id = ?',
            [$id]
        );
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->db->execute('UPDATE leads SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function assignTo(int $id, ?int $userId): void
    {
        $this->db->execute('UPDATE leads SET assigned_to = ? WHERE id = ?', [$userId, $id]);
    }

    /** @return array<int, array<string,mixed>> */
    public function notesFor(int $leadId): array
    {
        return $this->db->select(
            'SELECT ln.id, ln.body, ln.created_at, u.name AS author
               FROM lead_notes ln
               LEFT JOIN users u ON u.id = ln.user_id
              WHERE ln.lead_id = ?
              ORDER BY ln.created_at DESC',
            [$leadId]
        );
    }

    public function addNote(int $leadId, ?int $userId, string $body): int
    {
        $this->db->execute(
            'INSERT INTO lead_notes (lead_id, user_id, body) VALUES (?, ?, ?)',
            [$leadId, $userId, $body]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Stream-friendly export (used by CSV builder).
     * @return array<int, array<string,mixed>>
     */
    public function export(array $filters): array
    {
        [$where, $params] = $this->adminFilters($filters);
        $sql = "
            SELECT l.id, l.created_at, l.lead_type, l.status, l.source, l.locale,
                   l.name, l.phone, l.whatsapp, l.email, l.city, l.country,
                   l.message, l.vehicle_id,
                   CASE WHEN v.id IS NULL THEN NULL
                        ELSE CONCAT(b.name, ' ', m.name, ' ', v.year) END AS vehicle_label,
                   l.utm_source, l.utm_medium, l.utm_campaign, l.referrer
              FROM leads l
              LEFT JOIN vehicles v ON v.id = l.vehicle_id
              LEFT JOIN brands b   ON b.id = v.brand_id
              LEFT JOIN models m   ON m.id = v.model_id
            {$where}
             ORDER BY l.created_at DESC, l.id DESC
        ";
        $stmt = $this->db->pdo()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

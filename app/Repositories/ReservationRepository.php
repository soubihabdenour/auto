<?php

declare(strict_types=1);

namespace App\Repositories;

final class ReservationRepository extends BaseRepository
{
    private const INSERT_COLS = [
        'reference', 'vehicle_id', 'lead_id',
        'name', 'phone', 'whatsapp', 'email', 'city',
        'deposit_amount_usd', 'currency',
        'status', 'expires_at',
        'locale', 'ip_hash', 'user_agent',
    ];

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $placeholders = implode(',', array_fill(0, count(self::INSERT_COLS), '?'));
        $values = [];
        foreach (self::INSERT_COLS as $col) {
            $values[] = $data[$col] ?? null;
        }
        $this->db->execute(
            'INSERT INTO reservations (' . implode(',', self::INSERT_COLS) . ') VALUES (' . $placeholders . ')',
            $values
        );
        return (int) $this->db->lastInsertId();
    }

    public function referenceExists(string $reference): bool
    {
        $row = $this->db->selectOne(
            'SELECT 1 FROM reservations WHERE reference = ? LIMIT 1',
            [$reference]
        );
        return $row !== null;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        return $this->db->selectOne(
            $this->baseSelect() . ' WHERE r.id = ?',
            [$id]
        );
    }

    /** @return array<string, mixed>|null */
    public function findByReference(string $reference): ?array
    {
        return $this->db->selectOne(
            $this->baseSelect() . ' WHERE r.reference = ?',
            [$reference]
        );
    }

    /**
     * Active reservation for a vehicle, if any. Used to enforce the
     * "one active reservation per vehicle" invariant.
     *
     * @return array<string, mixed>|null
     */
    public function activeForVehicle(int $vehicleId): ?array
    {
        return $this->db->selectOne(
            "SELECT * FROM reservations
              WHERE vehicle_id = ?
                AND status IN ('pending_deposit','confirmed')
              ORDER BY id DESC
              LIMIT 1",
            [$vehicleId]
        );
    }

    public function markConfirmed(int $id, int $userId, ?string $adminNote): void
    {
        $this->db->execute(
            "UPDATE reservations
                SET status       = 'confirmed',
                    confirmed_at = NOW(),
                    confirmed_by = ?,
                    admin_note   = COALESCE(?, admin_note)
              WHERE id = ?
                AND status = 'pending_deposit'",
            [$userId, $adminNote, $id]
        );
    }

    public function markCancelled(int $id, int $userId, ?string $reason): void
    {
        $this->db->execute(
            "UPDATE reservations
                SET status              = 'cancelled',
                    cancelled_at        = NOW(),
                    cancelled_by        = ?,
                    cancellation_reason = ?
              WHERE id = ?
                AND status IN ('pending_deposit','confirmed')",
            [$userId, $reason, $id]
        );
    }

    public function markConverted(int $id): void
    {
        $this->db->execute(
            "UPDATE reservations
                SET status = 'converted'
              WHERE id = ?
                AND status = 'confirmed'",
            [$id]
        );
    }

    /**
     * Expire all pending_deposit reservations whose expires_at has passed.
     * Returns the list of affected reservations BEFORE updating so the caller
     * can revert the associated vehicle status.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchExpired(string $nowDatetime): array
    {
        return $this->db->select(
            "SELECT id, vehicle_id, reference, email, locale
               FROM reservations
              WHERE status = 'pending_deposit'
                AND expires_at <= ?",
            [$nowDatetime]
        );
    }

    /** @param array<int, int> $ids */
    public function markIdsExpired(array $ids): void
    {
        if ($ids === []) return;
        $in = implode(',', array_map('intval', $ids));
        $this->db->execute(
            "UPDATE reservations SET status = 'expired' WHERE id IN ({$in}) AND status = 'pending_deposit'"
        );
    }

    public function addAdminNote(int $id, string $note): void
    {
        $this->db->execute(
            'UPDATE reservations SET admin_note = ? WHERE id = ?',
            [$note, $id]
        );
    }

    // ---------- Admin listing ----------

    /**
     * @param array<string, mixed> $filters status, q, vehicle_id
     * @return array<int, array<string, mixed>>
     */
    public function adminList(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminFilters($filters);
        $sql = $this->baseSelect() . " {$where}
            ORDER BY r.created_at DESC, r.id DESC
            LIMIT :lim OFFSET :off";
        $stmt = $this->db->pdo()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @param array<string, mixed> $filters */
    public function adminCount(array $filters): int
    {
        [$where, $params] = $this->adminFilters($filters);
        $stmt = $this->db->pdo()->prepare("SELECT COUNT(*) AS n FROM reservations r {$where}");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['n'] ?? 0);
    }

    /**
     * Returns the SELECT clause with vehicle label joined in.
     * Keep it private — admin views go through adminList/findById.
     */
    private function baseSelect(): string
    {
        return "
            SELECT r.*,
                   v.slug AS vehicle_slug,
                   CASE WHEN v.id IS NULL THEN NULL
                        ELSE CONCAT(b.name, ' ', m.name, ' ', v.year) END AS vehicle_label
              FROM reservations r
              LEFT JOIN vehicles v ON v.id = r.vehicle_id
              LEFT JOIN brands   b ON b.id = v.brand_id
              LEFT JOIN models   m ON m.id = v.model_id
        ";
    }

    /**
     * @param array<string, mixed> $f
     * @return array{0:string,1:array<string,mixed>}
     */
    private function adminFilters(array $f): array
    {
        $w = []; $p = [];
        if (! empty($f['status']))     { $w[] = 'r.status = :status';     $p[':status']  = $f['status']; }
        if (! empty($f['vehicle_id'])) { $w[] = 'r.vehicle_id = :vid';    $p[':vid']     = (int) $f['vehicle_id']; }
        if (! empty($f['q'])) {
            $w[] = '(r.name LIKE :q OR r.phone LIKE :q OR r.email LIKE :q OR r.reference LIKE :q)';
            $p[':q'] = '%' . $f['q'] . '%';
        }
        $where = empty($w) ? '' : 'WHERE ' . implode(' AND ', $w);
        return [$where, $p];
    }
}

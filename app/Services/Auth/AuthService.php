<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Core\Database;
use App\Core\Session;

/**
 * Admin auth + login throttling.
 *
 *   - attempt($email, $password, $ip): bool — true if signed in
 *   - user(): ?array — currently-authenticated user row, or null
 *   - logout(): void
 *
 * Throttle: 5 failed attempts per (IP + email-hash) in a sliding 15-minute
 * window = 15-minute lockout. Verified attempts reset the counter.
 */
final class AuthService
{
    private const SESSION_KEY = 'user_id';

    public function __construct(
        private Database $db,
        private Session  $session,
        private int      $maxAttempts = 5,
        private int      $lockoutMinutes = 15,
    ) {}

    public function attempt(string $email, string $password, string $ip): bool
    {
        $email = strtolower(trim($email));
        $key   = $this->throttleKey($ip, $email);

        if ($this->isLocked($key)) {
            return false;
        }

        try {
            $user = $this->db->selectOne(
                'SELECT id, email, password_hash, name, role, is_active
                   FROM users
                  WHERE email = ?
                  LIMIT 1',
                [$email]
            );
        } catch (\Throwable) {
            // DB unavailable — fail closed
            return false;
        }

        if ($user === null || ! (bool) $user['is_active']) {
            $this->recordFailure($key);
            return false;
        }
        if (! password_verify($password, (string) $user['password_hash'])) {
            $this->recordFailure($key);
            return false;
        }

        // Success — regenerate session, persist user_id, reset throttle
        $this->session->regenerate(true);
        $this->session->put(self::SESSION_KEY, (int) $user['id']);
        $this->session->put('user_role',  (string) $user['role']);
        $this->session->put('user_name',  (string) $user['name']);
        $this->session->put('user_email', (string) $user['email']);
        $this->resetThrottle($key);

        $this->db->execute(
            'UPDATE users SET last_login_at = NOW() WHERE id = ?',
            [(int) $user['id']]
        );

        $this->writeAudit((int) $user['id'], 'auth.login', null, null, $ip);
        return true;
    }

    public function user(): ?array
    {
        $id = $this->session->get(self::SESSION_KEY);
        if (! is_int($id) && ! ctype_digit((string) $id)) {
            return null;
        }
        $id = (int) $id;
        try {
            return $this->db->selectOne(
                'SELECT id, email, name, role, is_active, last_login_at
                   FROM users
                  WHERE id = ? AND is_active = 1',
                [$id]
            );
        } catch (\Throwable) {
            return null;
        }
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function logout(string $ip = ''): void
    {
        $id = $this->session->get(self::SESSION_KEY);
        if ($id) {
            $this->writeAudit((int) $id, 'auth.logout', null, null, $ip);
        }
        $this->session->destroy();
    }

    public function writeAudit(?int $userId, string $action, ?string $entity, ?int $entityId, string $ip, array $payload = []): void
    {
        try {
            $this->db->execute(
                'INSERT INTO audit_logs (user_id, action, entity, entity_id, payload, ip)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$userId, $action, $entity, $entityId,
                 $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_UNICODE), $ip]
            );
        } catch (\Throwable) {
            // audit is best-effort; never breaks the request
        }
    }

    // ---------- Throttle ----------

    private function throttleKey(string $ip, string $email): string
    {
        return hash('sha256', $ip . '|' . $email);
    }

    private function isLocked(string $key): bool
    {
        try {
            $row = $this->db->selectOne(
                'SELECT attempts, locked_until FROM login_throttle WHERE `key` = ?',
                [$key]
            );
        } catch (\Throwable) {
            return false; // DB unavailable — fail open to surface the real error
        }
        if ($row === null) return false;
        if (! empty($row['locked_until']) && strtotime((string) $row['locked_until']) > time()) {
            return true;
        }
        return false;
    }

    private function recordFailure(string $key): void
    {
        try {
            $row = $this->db->selectOne(
                'SELECT id, attempts FROM login_throttle WHERE `key` = ?',
                [$key]
            );
            if ($row === null) {
                $this->db->execute(
                    'INSERT INTO login_throttle (`key`, attempts) VALUES (?, 1)',
                    [$key]
                );
                return;
            }
            $attempts = (int) $row['attempts'] + 1;
            $locked   = $attempts >= $this->maxAttempts
                ? date('Y-m-d H:i:s', time() + ($this->lockoutMinutes * 60))
                : null;
            $this->db->execute(
                'UPDATE login_throttle SET attempts = ?, locked_until = ? WHERE id = ?',
                [$attempts, $locked, (int) $row['id']]
            );
        } catch (\Throwable) {
            // best-effort
        }
    }

    private function resetThrottle(string $key): void
    {
        try {
            $this->db->execute(
                'DELETE FROM login_throttle WHERE `key` = ?',
                [$key]
            );
        } catch (\Throwable) {}
    }
}

<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private ?PDO $pdo = null;

    /** @param array<string, mixed> $config */
    public function __construct(private array $config) {}

    public function pdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['driver']   ?? 'mysql',
            $this->config['host']     ?? '127.0.0.1',
            (int) ($this->config['port'] ?? 3306),
            $this->config['database'] ?? '',
            $this->config['charset']  ?? 'utf8mb4'
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                (string) ($this->config['username'] ?? ''),
                (string) ($this->config['password'] ?? ''),
                $this->config['options'] ?? []
            );
            // Enforce session collation to match table collation
            $collation = $this->config['collation'] ?? 'utf8mb4_unicode_ci';
            $this->pdo->exec("SET NAMES utf8mb4 COLLATE {$collation}");
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        return $this->pdo;
    }

    /** @return array<int, array<string, mixed>> */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo()->lastInsertId();
    }

    public function beginTransaction(): void { $this->pdo()->beginTransaction(); }
    public function commit(): void           { $this->pdo()->commit(); }
    public function rollback(): void         { $this->pdo()->rollBack(); }
}

<?php

declare(strict_types=1);

/**
 * Daily DB backup — mysqldump → gzip → storage/backups/.
 * Designed for crontab:
 *   0 3 * * * cd /path/to/korea-auto-export && /usr/bin/php bin/backup.php >> storage/logs/backup.log 2>&1
 *
 * Keeps the most recent 30 daily backups + 12 monthly backups.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/bootstrap/autoload.php';
$container = require BASE_PATH . '/bootstrap/app.php';
/** @var \App\Core\Config $config */
$config = $container->get(\App\Core\Config::class);

$db    = (array) $config->get('database', []);
$host  = (string) ($db['host']     ?? '127.0.0.1');
$port  = (int)    ($db['port']     ?? 3306);
$name  = (string) ($db['database'] ?? 'koreaautoexport');
$user  = (string) ($db['username'] ?? 'root');
$pass  = (string) ($db['password'] ?? '');

$dir = BASE_PATH . '/storage/backups';
if (! is_dir($dir)) { mkdir($dir, 0755, true); }

$date  = date('Y-m-d_His');
$file  = "{$dir}/kae_{$date}.sql.gz";
$log   = "[" . date('c') . "]";

// Build mysqldump command. Use --defaults-file to avoid showing the password
// on the process list; fall back to a plain command if writing the temp file fails.
$cnfPath = tempnam(sys_get_temp_dir(), 'kae_my_');
$cnf = sprintf(
    "[client]\nhost=%s\nport=%d\nuser=%s\npassword=%s\n",
    $host, $port, $user, $pass
);
file_put_contents($cnfPath, $cnf);
chmod($cnfPath, 0600);

$cmd = sprintf(
    'mysqldump --defaults-file=%s --single-transaction --quick --routines --triggers %s | gzip -c > %s',
    escapeshellarg($cnfPath),
    escapeshellarg($name),
    escapeshellarg($file)
);

echo "{$log} backup start: {$file}\n";
exec($cmd, $output, $code);
@unlink($cnfPath);

if ($code !== 0 || ! is_file($file) || filesize($file) === 0) {
    fwrite(STDERR, "{$log} ✗ backup failed (exit={$code}).\n");
    if (is_file($file)) @unlink($file);
    exit(1);
}

$bytes = filesize($file);
echo "{$log} ✓ backup wrote " . number_format($bytes) . " bytes\n";

// ---- retention --------------------------------------------------
// Keep last 30 daily files + the first-of-month for the last 12 months.
$all = glob($dir . '/kae_*.sql.gz') ?: [];
rsort($all);

$keep = array_slice($all, 0, 30);
$byMonth = [];
foreach ($all as $f) {
    if (preg_match('/kae_(\d{4})-(\d{2})-(\d{2})_/', basename($f), $m)) {
        $monthKey = "{$m[1]}-{$m[2]}";
        if (! isset($byMonth[$monthKey])) $byMonth[$monthKey] = $f;
    }
}
$keep = array_unique(array_merge($keep, array_slice(array_values($byMonth), 0, 12)));

$deleted = 0;
foreach ($all as $f) {
    if (! in_array($f, $keep, true)) {
        if (@unlink($f)) $deleted++;
    }
}
echo "{$log} retention: kept " . count($keep) . " files, deleted {$deleted}\n";

// ---- log rotation ------------------------------------------------
$logFile = BASE_PATH . '/storage/logs/php_error.log';
if (is_file($logFile) && filesize($logFile) > 5 * 1024 * 1024) {
    $rotated = $logFile . '.' . date('Y-m-d');
    @rename($logFile, $rotated);
    touch($logFile);
    echo "{$log} log rotated → {$rotated}\n";
}

exit(0);

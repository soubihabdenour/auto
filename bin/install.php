<?php

declare(strict_types=1);

/**
 * Korea Auto Export — installation script
 *
 *   php bin/install.php             # schema + seeds + admin
 *   php bin/install.php --with-demo # also load 5 demo vehicles + testimonials
 *   php bin/install.php --admin-only# only create/reset admin
 *   php bin/install.php --no-admin  # only schema + seeds, skip admin
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/bootstrap/autoload.php';

$argvOpts = array_slice($argv, 1);
$adminOnly = in_array('--admin-only', $argvOpts, true);
$noAdmin   = in_array('--no-admin',   $argvOpts, true);
$withDemo  = in_array('--with-demo',  $argvOpts, true);

/** @var \App\Core\Container $container */
$container = require BASE_PATH . '/bootstrap/app.php';
/** @var \App\Core\Database $db */
$db = $container->get(\App\Core\Database::class);

echo "Korea Auto Export — installer\n";
echo "──────────────────────────────\n";

try {
    if (! $adminOnly) {
        runSqlFile($db, BASE_PATH . '/database/schema.sql', 'schema');
        runSqlFile($db, BASE_PATH . '/database/seeds/00_reference.sql', 'reference seed');
        if ($withDemo) {
            runSqlFile($db, BASE_PATH . '/database/seeds/01_demo.sql', 'demo data (5 vehicles, testimonials)');
        }
    }
    if (! $noAdmin) {
        createOrUpdateAdmin($db);
    }
    echo "\n✓ Done.\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "\n✗ Installation failed: " . $e->getMessage() . "\n");
    if ((bool) ($container->get(\App\Core\Config::class)->get('app.debug'))) {
        fwrite(STDERR, $e->getTraceAsString() . "\n");
    }
    exit(1);
}

function runSqlFile(\App\Core\Database $db, string $path, string $label): void
{
    if (! is_file($path)) {
        throw new RuntimeException("SQL file not found: {$path}");
    }
    echo "→ Running {$label} (" . basename($path) . ") ...";
    $sql = (string) file_get_contents($path);
    // Strip /* ... */ and -- comments, then split on ; at end of line.
    $cleaned = preg_replace('#/\*.*?\*/#s', '', $sql) ?? $sql;
    $cleaned = preg_replace('/^\s*--.*$/m', '', $cleaned) ?? $cleaned;
    $statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/', $cleaned) ?: []));
    $count = 0;
    foreach ($statements as $stmt) {
        if ($stmt === '' || $stmt === ';') {
            continue;
        }
        $db->pdo()->exec($stmt);
        $count++;
    }
    echo " ok ({$count} statements)\n";
}

function createOrUpdateAdmin(\App\Core\Database $db): void
{
    $email = promptDefault('Admin email', 'admin@koreaautoexport.dz');
    $name  = promptDefault('Admin name', 'Site Administrator');
    $pass  = promptPassword('Admin password (min 12 chars)');
    if (strlen($pass) < 12) {
        throw new RuntimeException('Password must be at least 12 characters.');
    }
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

    $existing = $db->selectOne('SELECT id FROM users WHERE email = ?', [$email]);
    if ($existing) {
        $db->execute(
            'UPDATE users SET password_hash = ?, name = ?, role = ?, is_active = 1 WHERE id = ?',
            [$hash, $name, 'admin', $existing['id']]
        );
        echo "→ Admin user updated: {$email}\n";
        return;
    }
    $db->execute(
        'INSERT INTO users (email, password_hash, name, role, is_active) VALUES (?, ?, ?, ?, 1)',
        [$email, $hash, $name, 'admin']
    );
    echo "→ Admin user created: {$email}\n";
}

function promptDefault(string $label, string $default): string
{
    echo "{$label} [{$default}]: ";
    $input = trim((string) fgets(STDIN));
    return $input === '' ? $default : $input;
}

function promptPassword(string $label): string
{
    echo "{$label}: ";
    if (DIRECTORY_SEPARATOR !== '\\' && shell_exec('which stty')) {
        shell_exec('stty -echo');
        $input = trim((string) fgets(STDIN));
        shell_exec('stty echo');
        echo "\n";
        return $input;
    }
    return trim((string) fgets(STDIN));
}

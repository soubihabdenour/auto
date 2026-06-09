<?php

declare(strict_types=1);

/**
 * Cron — expire overdue pending reservations and notify the customer.
 *
 * Crontab (hourly is plenty for a 48h window):
 *   0 * * * * cd /path/to/koreaautoexport && /usr/bin/php bin/expire-reservations.php >> storage/logs/reservations.log 2>&1
 *
 * Each invocation:
 *   1. ReservationService::expireDue() flips overdue pending_deposit rows
 *      to 'expired' and returns their vehicles to 'available'.
 *   2. For each expired row that has an email, the mailer sends a
 *      "reservation expired" notice. Mailing is best-effort — if it
 *      throws we still consider the expiry a success and move on.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/bootstrap/autoload.php';
/** @var \App\Core\Container $container */
$container = require BASE_PATH . '/bootstrap/app.php';

/** @var \App\Services\Reservation\ReservationService $service */
$service = $container->get(\App\Services\Reservation\ReservationService::class);
/** @var \App\Services\Reservation\ReservationMailer $mailer */
$mailer  = $container->get(\App\Services\Reservation\ReservationMailer::class);
/** @var \App\Repositories\ReservationRepository $repo */
$repo    = $container->get(\App\Repositories\ReservationRepository::class);
/** @var \App\Core\Config $config */
$config  = $container->get(\App\Core\Config::class);

$ts = date('Y-m-d H:i:s');
echo "[{$ts}] expire-reservations: scanning…\n";

try {
    $expired = $service->expireDue();
} catch (\Throwable $e) {
    fwrite(STDERR, "[{$ts}] FATAL: " . $e->getMessage() . "\n");
    exit(1);
}

if ($expired === []) {
    echo "[{$ts}] nothing to expire.\n";
    exit(0);
}

$siteUrl = rtrim((string) $config->get('app.url', ''), '/');
$sent = 0; $skipped = 0;
foreach ($expired as $row) {
    if (empty($row['email'])) { $skipped++; continue; }
    $full = $repo->findById((int) $row['id']);
    if ($full === null) { $skipped++; continue; }
    $statusUrl = $siteUrl . '/' . $row['locale'] . '/reservations/' . $row['reference'];
    try {
        $mailer->expired($full, $statusUrl);
        $sent++;
    } catch (\Throwable $e) {
        fwrite(STDERR, "[{$ts}] mailer error for {$row['reference']}: " . $e->getMessage() . "\n");
        $skipped++;
    }
}

printf("[%s] expired=%d mailed=%d skipped=%d\n", $ts, count($expired), $sent, $skipped);
exit(0);

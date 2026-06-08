<?php

declare(strict_types=1);

/**
 * Generate an APP_KEY suitable for the .env file.
 *
 *   php bin/keygen.php
 *
 * If --write is passed, the script overwrites APP_KEY in the existing .env.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

$key = bin2hex(random_bytes(32));

if (in_array('--write', $argv, true)) {
    $envPath = dirname(__DIR__) . '/.env';
    if (! is_file($envPath)) {
        fwrite(STDERR, ".env not found at {$envPath}\n");
        exit(1);
    }
    $contents = (string) file_get_contents($envPath);
    if (preg_match('/^APP_KEY=.*$/m', $contents)) {
        $contents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $contents);
    } else {
        $contents .= "\nAPP_KEY={$key}\n";
    }
    file_put_contents($envPath, $contents, LOCK_EX);
    echo "✓ APP_KEY written to .env\n";
    exit(0);
}

echo $key . PHP_EOL;

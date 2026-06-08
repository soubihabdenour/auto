<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Exception\HttpException;
use App\Core\Request;
use App\Core\Response;
use Closure;

/**
 * Filesystem-backed rate limit. Phase 2 will mount this on /inquiry routes.
 * Bucket key = sha256(IP|route_path).
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $storagePath,
        private int    $maxPerHour = 5,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $key = hash('sha256', $request->ip() . '|' . $request->path);
        $file = rtrim($this->storagePath, '/') . '/rl_' . $key . '.json';

        $now    = time();
        $window = 3600;

        $bucket = ['ts' => $now, 'count' => 0];
        if (is_file($file)) {
            $raw = (string) @file_get_contents($file);
            $parsed = json_decode($raw, true);
            if (is_array($parsed) && isset($parsed['ts'], $parsed['count'])) {
                $bucket = $parsed;
            }
        }

        if ($now - (int) $bucket['ts'] > $window) {
            $bucket = ['ts' => $now, 'count' => 0];
        }

        $bucket['count']++;
        @file_put_contents($file, json_encode($bucket), LOCK_EX);

        if ($bucket['count'] > $this->maxPerHour) {
            throw new HttpException(429, 'Too many requests. Please try again later.');
        }

        return $next($request);
    }
}

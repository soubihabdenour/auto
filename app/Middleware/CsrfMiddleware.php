<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Exception\HttpException;
use App\Core\Request;
use App\Core\Response;
use Closure;

final class CsrfMiddleware implements MiddlewareInterface
{
    private const UNSAFE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(private Csrf $csrf) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method, self::UNSAFE_METHODS, true)) {
            $token = (string) $request->input('_csrf', $request->header('X-CSRF-Token', ''));
            if (! $this->csrf->verify($token)) {
                throw new HttpException(419, 'CSRF token mismatch');
            }
        }

        return $next($request);
    }
}

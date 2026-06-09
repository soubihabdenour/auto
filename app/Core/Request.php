<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @var array<string, string> */
    private array $routeParams = [];

    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array  $query,
        public readonly array  $post,
        public readonly array  $files,
        public readonly array  $cookies,
        public readonly array  $server,
    ) {}

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Method override (for HTML forms emulating PUT/PATCH/DELETE)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        $uri  = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            $path = '/';
        }

        return new self(
            method:  $method,
            path:    $path,
            query:   $_GET    ?? [],
            post:    $_POST   ?? [],
            files:   $_FILES  ?? [],
            cookies: $_COOKIE ?? [],
            server:  $_SERVER ?? [],
        );
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function routeParams(): array
    {
        return $this->routeParams;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function isMethod(string $method): bool
    {
        return strcasecmp($this->method, $method) === 0;
    }

    public function isAjax(): bool
    {
        return strtolower((string) ($this->server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($this->server[$key]) ? (string) $this->server[$key] : $default;
    }

    public function ip(): string
    {
        // Trust X-Forwarded-For only behind a known proxy in production; here we keep it simple.
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return (string) ($this->server['HTTP_USER_AGENT'] ?? '');
    }

    public function url(): string
    {
        $scheme = ! empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off' ? 'https' : 'http';
        $host   = (string) ($this->server['HTTP_HOST'] ?? 'localhost');
        return $scheme . '://' . $host . $this->path;
    }
}

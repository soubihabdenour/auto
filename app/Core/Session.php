<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private bool $started = false;

    /** @param array<string, mixed> $config */
    public function __construct(private array $config) {}

    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        $name      = (string) ($this->config['name']      ?? 'kae_session');
        $lifetime  = (int)    ($this->config['lifetime']  ?? 7200);
        $secure    = (bool)   ($this->config['secure']    ?? false);
        $samesite  = (string) ($this->config['samesite']  ?? 'Lax');
        $savePath  = (string) ($this->config['save_path'] ?? '');

        if ($savePath !== '' && is_writable($savePath)) {
            session_save_path($savePath);
        }

        session_name($name);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => $samesite,
        ]);

        session_start([
            'use_strict_mode' => 1,
            'cookie_secure'   => $secure,
            'cookie_httponly' => true,
        ]);
        $this->started = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->start();
        return array_key_exists($key, $_SESSION);
    }

    public function forget(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }

    public function flash(string $key, mixed $value): void
    {
        $this->start();
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->start();
        if (! isset($_SESSION['_flash'][$key])) {
            return $default;
        }
        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        $this->start();
        session_regenerate_id($deleteOldSession);
    }

    public function destroy(): void
    {
        $this->start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        $this->started = false;
    }
}

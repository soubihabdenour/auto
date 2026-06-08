<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public function __construct(private Session $session) {}

    public function token(): string
    {
        $token = $this->session->get(self::SESSION_KEY);
        if (! is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->put(self::SESSION_KEY, $token);
        }
        return $token;
    }

    public function verify(?string $candidate): bool
    {
        if ($candidate === null || $candidate === '') {
            return false;
        }
        $expected = $this->session->get(self::SESSION_KEY);
        return is_string($expected) && hash_equals($expected, $candidate);
    }

    public function rotate(): string
    {
        $this->session->forget(self::SESSION_KEY);
        return $this->token();
    }
}

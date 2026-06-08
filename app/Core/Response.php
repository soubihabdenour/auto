<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private int $status = 200;
    /** @var array<string, string> */
    private array $headers = [];
    private string $body = '';

    public function status(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public static function html(string $html, int $status = 200): self
    {
        return (new self())
            ->status($status)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->body($html);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return (new self())
            ->status($status)
            ->header('Content-Type', 'application/json; charset=UTF-8')
            ->body((string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function redirect(string $to, int $status = 302): self
    {
        return (new self())
            ->status($status)
            ->header('Location', $to);
    }

    public static function text(string $text, int $status = 200): self
    {
        return (new self())
            ->status($status)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->body($text);
    }

    public function send(): void
    {
        if (! headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }
        echo $this->body;
    }
}

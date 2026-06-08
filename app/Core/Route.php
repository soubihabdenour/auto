<?php

declare(strict_types=1);

namespace App\Core;

final class Route
{
    public ?string $name = null;
    /** @var array<int, string> */
    public array $middleware = [];

    public function __construct(
        public readonly string $method,
        public readonly string $pattern,
        public readonly mixed  $action,
        public readonly string $regex,
        public readonly array  $paramNames,
    ) {}

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function middleware(string ...$middleware): self
    {
        array_push($this->middleware, ...$middleware);
        return $this;
    }
}

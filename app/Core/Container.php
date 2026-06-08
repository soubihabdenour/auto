<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use RuntimeException;

/**
 * Minimal DI container. Supports:
 *   - bind(abstract, concrete|closure)
 *   - singleton(abstract, concrete|closure)
 *   - instance(abstract, object)
 *   - get(abstract)
 */
final class Container
{
    /** @var array<string, array{concrete: mixed, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared'   => $shared,
        ];
    }

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) || class_exists($abstract);
    }

    public function get(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding  = $this->bindings[$abstract] ?? null;
        $concrete = $binding['concrete'] ?? $abstract;
        $shared   = $binding['shared'] ?? false;

        $object = $concrete instanceof Closure
            ? $concrete($this)
            : $this->build($concrete);

        if ($shared) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function build(string $class): object
    {
        if (! class_exists($class)) {
            throw new RuntimeException("Container: class {$class} does not exist.");
        }

        $reflection = new \ReflectionClass($class);
        if (! $reflection->isInstantiable()) {
            throw new RuntimeException("Container: {$class} is not instantiable.");
        }

        $ctor = $reflection->getConstructor();
        if ($ctor === null) {
            return new $class();
        }

        $args = [];
        foreach ($ctor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                $args[] = $this->get($type->getName());
                continue;
            }
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }
            throw new RuntimeException(
                "Container: cannot resolve parameter \${$param->getName()} of {$class}."
            );
        }

        return $reflection->newInstanceArgs($args);
    }
}

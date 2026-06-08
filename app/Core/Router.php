<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exception\NotFoundException;
use Closure;
use RuntimeException;

final class Router
{
    /** @var array<int, Route> */
    private array $routes = [];

    /** @var array<int, array{prefix?: string, middleware?: array<int,string>}> */
    private array $groupStack = [];

    public function __construct(private Container $container) {}

    public function get(string $pattern, mixed $action): Route    { return $this->add('GET',    $pattern, $action); }
    public function post(string $pattern, mixed $action): Route   { return $this->add('POST',   $pattern, $action); }
    public function put(string $pattern, mixed $action): Route    { return $this->add('PUT',    $pattern, $action); }
    public function patch(string $pattern, mixed $action): Route  { return $this->add('PATCH',  $pattern, $action); }
    public function delete(string $pattern, mixed $action): Route { return $this->add('DELETE', $pattern, $action); }

    /**
     * @param array{prefix?: string, middleware?: array<int,string>} $attributes
     */
    public function group(array $attributes, Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function add(string $method, string $pattern, mixed $action): Route
    {
        $prefix      = '';
        $middlewares = [];
        foreach ($this->groupStack as $group) {
            $prefix      .= $group['prefix'] ?? '';
            $middlewares  = array_merge($middlewares, $group['middleware'] ?? []);
        }

        $fullPattern = $prefix . $pattern;
        $fullPattern = '/' . trim($fullPattern, '/');
        if ($fullPattern === '/') {
            // keep "/"
        }

        [$regex, $params] = $this->compile($fullPattern);

        $route = new Route($method, $fullPattern, $action, $regex, $params);
        if (! empty($middlewares)) {
            $route->middleware(...$middlewares);
        }

        $this->routes[] = $route;
        return $route;
    }

    /**
     * Convert "/{locale}/vehicles/{slug}" → ['#^/(?P<locale>[^/]+)/vehicles/(?P<slug>[^/]+)$#', ['locale','slug']]
     *
     * @return array{0:string,1:array<int,string>}
     */
    private function compile(string $pattern): array
    {
        $names = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            function (array $m) use (&$names): string {
                $names[] = $m[1];
                return '(?P<' . $m[1] . '>[^/]+)';
            },
            $pattern
        ) ?? $pattern;

        return ['#^' . $regex . '$#u', $names];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if (! $this->methodMatches($route, $request->method)) {
                continue;
            }
            if (! preg_match($route->regex, $request->path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($route->paramNames as $name) {
                $params[$name] = isset($matches[$name]) ? rawurldecode((string) $matches[$name]) : '';
            }
            $request->setRouteParams($params);

            return $this->runWithMiddleware($route, $request);
        }

        throw new NotFoundException("No route matches {$request->method} {$request->path}");
    }

    private function methodMatches(Route $route, string $method): bool
    {
        return $route->method === $method
            || ($route->method === 'GET' && $method === 'HEAD');
    }

    private function runWithMiddleware(Route $route, Request $request): Response
    {
        // Build the middleware pipeline; innermost is the controller invocation.
        $core = function (Request $req) use ($route): Response {
            return $this->invokeAction($route->action, $req);
        };

        $pipeline = array_reduce(
            array_reverse($route->middleware),
            function (Closure $next, string $mwClass): Closure {
                return function (Request $req) use ($next, $mwClass): Response {
                    /** @var \App\Middleware\MiddlewareInterface $mw */
                    $mw = $this->container->get($mwClass);
                    return $mw->handle($req, $next);
                };
            },
            $core
        );

        return $pipeline($request);
    }

    private function invokeAction(mixed $action, Request $request): Response
    {
        if ($action instanceof Closure) {
            $result = $action($request);
        } elseif (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;
            $controller = $this->container->get($class);
            $result = $controller->{$method}($request);
        } else {
            throw new RuntimeException('Router: unsupported action type.');
        }

        if ($result instanceof Response) {
            return $result;
        }
        if (is_string($result)) {
            return Response::html($result);
        }
        if (is_array($result) || is_object($result)) {
            return Response::json($result);
        }
        return Response::html('');
    }

    /** @return array<int, Route> */
    public function all(): array
    {
        return $this->routes;
    }

    public function urlFor(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->name !== $name) {
                continue;
            }
            $url = $route->pattern;
            foreach ($params as $k => $v) {
                $url = str_replace('{' . $k . '}', (string) $v, $url);
            }
            return $url;
        }
        throw new RuntimeException("Router: no route named {$name}.");
    }
}

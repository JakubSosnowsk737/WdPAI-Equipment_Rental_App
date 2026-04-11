<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Bardzo prosty router - mapowanie [METHOD, PATH] -> [Klasa, metoda].
 * Obsluguje placeholdery {id} w sciezce.
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:array{0:string,1:string}, middleware: array<int,callable>}> */
    private array $routes = [];

    public function add(string $method, string $path, array $handler, array $middleware = []): void
    {
        $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }
            if (!preg_match($route['pattern'], $request->path(), $m)) {
                continue;
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $mw) {
                $mw($request);
            }

            [$class, $action] = $route['handler'];
            if (!class_exists($class)) {
                throw new RuntimeException("Brak kontrolera $class");
            }
            $controller = new $class();
            $controller->$action($params);
            return;
        }

        http_response_code(404);
        echo '404 - nie znaleziono';
    }
}

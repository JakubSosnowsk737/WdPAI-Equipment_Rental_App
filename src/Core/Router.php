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

    /** @var array<int, callable> middleware uruchamiany dla kazdej trasy przed middleware trasy */
    private array $globalMiddleware = [];

    public function addGlobalMiddleware(callable $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }

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
        $pathMatchedButMethodNot = false;

        foreach ($this->routes as $route) {
            $matchesPath = (bool) preg_match($route['pattern'], $request->path(), $m);
            if (!$matchesPath) {
                continue;
            }
            if ($route['method'] !== $request->method()) {
                // Sciezka istnieje, ale metoda sie nie zgadza (np. GET na endpoint POST).
                $pathMatchedButMethodNot = true;
                continue;
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($this->globalMiddleware as $mw) {
                $mw($request);
            }
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

        // Metoda niedozwolona dla istniejacej sciezki -> 405; w przeciwnym razie 404.
        ErrorHandler::renderErrorPage($pathMatchedButMethodNot ? 405 : 404);
    }
}

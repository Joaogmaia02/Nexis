<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->routes['GET'][$path] = [$controller, $method];
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->routes['POST'][$path] = [$controller, $method];
    }

    public function dispatch(string $requestMethod, string $path): void
    {
        $normalizedPath = $path === '' ? '/' : $path;
        $route = $this->routes[$requestMethod][$normalizedPath] ?? null;

        if ($route === null) {
            http_response_code(404);
            echo '404 - Rota não encontrada';
            return;
        }

        [$controllerClass, $method] = $route;
        $controller = new $controllerClass();
        $controller->$method();
    }
}

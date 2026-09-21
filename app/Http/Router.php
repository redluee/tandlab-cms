<?php

declare(strict_types=1);

namespace App\Http;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = ['method' => strtoupper($method), 'path' => $path, 'handler' => $handler];
    }

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $path !== '/' ? rtrim($path, '/') : $path;

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            $params = $this->match($route['path'], $path);
            if ($params !== null) {
                call_user_func($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../views/public/404.php';
    }

    private function match(string $routePath, string $path): ?array
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($routeParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[substr($part, 1, -1)] = $pathParts[$i];
            } elseif ($part !== $pathParts[$i]) {
                return null;
            }
        }

        return $params;
    }
}

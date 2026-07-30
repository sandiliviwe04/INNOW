<?php

namespace Innow;

use Innow\Controllers\AuthController;
use Innow\Controllers\AttendanceController;
use Innow\Controllers\DashboardController;
use Innow\Controllers\UserController;
use Innow\Utils\ResponseHelper;

class Router {
    private array $routes = [];

    public function get(string $path, callable|array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void {
        $normalizedPath = rtrim($path, '/');
        if ($normalizedPath === '') {
            $normalizedPath = '/';
        }
        $this->routes[] = [
            'method' => $method,
            'path' => $normalizedPath,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = rtrim($path, '/');
        if ($path === '') $path = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $handler = $route['handler'];

                if (is_array($handler)) {
                    list($class, $methodName) = $handler;
                    $controller = new $class();
                    $controller->$methodName();
                    return;
                } else if (is_callable($handler)) {
                    call_user_func($handler);
                    return;
                }
            }
        }

        // 404 handler
        if (str_starts_with($path, '/api/')) {
            ResponseHelper::error("API endpoint {$path} not found.", 404);
        } else {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>The path {$path} does not exist on INNOW Server.</p>";
        }
    }
}

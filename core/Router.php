<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, string $controller, string $action, array $middleware = []): void {
        $this->addRoute('GET', $path, $controller, $action, $middleware);
    }

    public function post(string $path, string $controller, string $action, array $middleware = []): void {
        $this->addRoute('POST', $path, $controller, $action, $middleware);
    }

    private function addRoute(string $method, string $path, string $controller, string $action, array $middleware): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->normalizePath($path),
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    public function dispatch(string $method, string $uri): void {
        $uri = $this->normalizePath($uri);
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->match($route['path'], $uri, $params)) {
                
                // 1. Process Middleware
                foreach ($route['middleware'] as $mw) {
                    $middlewareInstance = new $mw();
                    if (!$middlewareInstance->handle()) {
                        return; // Request blocked by middleware
                    }
                }

                // 2. Instantiate Controller
                $controllerName = $route['controller'];
                $actionName = $route['action'];

                if (class_exists($controllerName)) {
                    $controllerInstance = new $controllerName();
                    if (method_exists($controllerInstance, $actionName)) {
                        call_user_func_array([$controllerInstance, $actionName], $params);
                        return;
                    }
                }

                http_response_code(500);
                echo "500 Internal Server Error: Method $actionName not found in $controllerName.";
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    private function normalizePath(string $path): string {
        $path = trim($path, '/');
        return $path === '' ? '/' : '/' . $path;
    }

    private function match(string $routePath, string $requestUri, &$params = []): bool {
        // Simple regex matching for dynamic parameters {id}
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestUri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }
}

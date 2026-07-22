<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    protected array $routes = [];

    public function get(string $uri, array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute(string $method, string $uri, array $action): void
    {
        $this->routes[$method][] = [
            'uri' => $this->normalize($uri),
            'action' => $action
        ];
    }

    public function dispatch(): void
    {
        $request = new Request();

        $method = $request->method();
        $uri = $this->normalize($request->uri());

        if (!isset($this->routes[$method])) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        foreach ($this->routes[$method] as $route) {

            $pattern = preg_replace(
                '/\{([a-zA-Z0-9_]+)\}/',
                '([^\/]+)',
                $route['uri']
            );

            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {

                array_shift($matches);

                [$controller, $function] = $route['action'];

                $instance = new $controller();

                call_user_func_array([$instance, $function], $matches);

                return;
            }
        }

        http_response_code(404);

        View::render('errors/404');
    }

    private function normalize(string $uri): string
    {
        if ($uri === '/') {
            return '/';
        }

        return '/' . trim($uri, '/');
    }
}
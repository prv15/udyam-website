<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\App as AppConfig;
use Throwable;

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
            'uri'    => $this->normalize($uri),
            'action' => $action
        ];
    }

    /**
     * Dispatch Request
     */
    public function dispatch(): void
    {
        $request = new Request();

        $method = $request->method();
        $uri    = $this->normalize($request->uri());


        if (!isset($this->routes[$method])) {
            $this->abort404();
            return;
        }

        foreach ($this->routes[$method] as $route) {

            $pattern = preg_replace(
                '/\{([a-zA-Z0-9_]+)\}/',
                '([^\/]+)',
                $route['uri']
            );

            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            array_shift($matches);

            [$controller, $method] = $route['action'];

            if (!class_exists($controller)) {
                throw new \RuntimeException(
                    "Controller {$controller} not found."
                );
            }

            if (!method_exists($controller, $method)) {
                throw new \RuntimeException(
                    "Method {$method} does not exist in {$controller}."
                );
            }

            try {

                // Resolve Controller from DI Container
                $instance = App::container()->get($controller);
                $reflection = new \ReflectionMethod($instance, $method);
                foreach ($reflection->getParameters() as $index => $parameter) {
                    if (!array_key_exists($index, $matches)) {
                        continue;
                    }
                    $type = $parameter->getType();
                    if ($type instanceof \ReflectionNamedType && $type->getName() === 'int') {
                        $matches[$index] = (int) $matches[$index];
                    }
                }

                call_user_func_array(
                    [$instance, $method],
                    $matches
                );

            } catch (Throwable $e) {

                if (AppConfig::debug()) {
                    throw $e;
                }

                http_response_code(500);

                View::render('errors/500');
            }

            return;
        }

        $this->abort404();
    }

    /**
     * Normalize URI
     */
    private function normalize(string $uri): string
    {
        if ($uri === '/') {
            return '/';
        }

        return '/' . trim($uri, '/');
    }

    /**
     * 404 Response
     */
    private function abort404(): void
    {
        http_response_code(404);

        View::render('errors/404');
    }
}

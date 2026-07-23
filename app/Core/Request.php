<?php

declare(strict_types=1);

namespace App\Core;
use App\Config\App;

final class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;

    public function __construct()
    {
        $this->get    = $_GET;
        $this->post   = $_POST;
        $this->files  = $_FILES;
        $this->server = $_SERVER;
    }

    /**
     * Request Method
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Current URI
     */
    public function uri(): string
{
    $uri = parse_url(
        $this->server['REQUEST_URI'] ?? '/',
        PHP_URL_PATH
    ) ?: '/';

    $basePath = App::basePath();

    if ($basePath !== '' && str_starts_with($uri, $basePath)) {
        $uri = substr($uri, strlen($basePath));
    }

    return $uri === '' ? '/' : $uri;
}

    /**
     * All Input
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * Input Value
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key]
            ?? $this->get[$key]
            ?? $default;
    }

    /**
     * Query Parameter
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Post Parameter
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Check if Input Exists
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->post)
            || array_key_exists($key, $this->get);
    }

    /**
     * Check if Filled
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key);

        return $value !== null
            && $value !== '';
    }

    /**
     * Integer Input
     */
    public function integer(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    /**
     * Float Input
     */
    public function float(string $key, float $default = 0): float
    {
        return (float) $this->input($key, $default);
    }

    /**
     * Boolean Input
     */
    public function boolean(string $key): bool
    {
        return filter_var(
            $this->input($key),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * String Input
     */
    public function string(string $key, string $default = ''): string
    {
        return trim((string) $this->input($key, $default));
    }

    /**
     * Return only selected fields
     */
    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            if ($this->has($key)) {
                $data[$key] = $this->input($key);
            }
        }

        return $data;
    }

    /**
     * Return all except selected fields
     */
    public function except(array $keys): array
    {
        return array_diff_key(
            $this->all(),
            array_flip($keys)
        );
    }

    /**
     * Uploaded File
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * All Uploaded Files
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Is POST
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Is GET
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * AJAX Request
     */
    public function isAjax(): bool
    {
        return strtolower(
            $this->server['HTTP_X_REQUESTED_WITH'] ?? ''
        ) === 'xmlhttprequest';
    }

    /**
     * Client IP
     */
    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '';
    }

    /**
     * User Agent
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * HTTP Referer
     */
    public function referer(): ?string
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }

    /**
     * Current URL
     */
    public function fullUrl(): string
    {
        return ($this->server['REQUEST_SCHEME'] ?? 'http')
            . '://'
            . ($this->server['HTTP_HOST'] ?? '')
            . ($this->server['REQUEST_URI'] ?? '');
    }
}
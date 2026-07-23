<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Config
{
    /**
     * Loaded configuration cache.
     *
     * @var array<string, array>
     */
    private static array $loaded = [];

    /**
     * Get configuration value.
     *
     * Examples:
     *
     * Config::get('app');
     * Config::get('app.name');
     * Config::get('database.default');
     * Config::get('media.image.quality');
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);

        $file = array_shift($segments);

        if (!$file) {
            return $default;
        }

        $config = self::load($file);

        if (empty($segments)) {
            return $config;
        }

        foreach ($segments as $segment) {

            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }

            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Determine whether a config key exists.
     */
    public static function has(string $key): bool
    {
        return self::get($key, '__missing__') !== '__missing__';
    }

    /**
     * Load configuration file once.
     *
     * @throws RuntimeException
     */
    private static function load(string $file): array
    {
        if (isset(self::$loaded[$file])) {
            return self::$loaded[$file];
        }

        $path = CONFIG_PATH . '/' . $file . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException(
                "Configuration file '{$file}.php' not found."
            );
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new RuntimeException(
                "Configuration file '{$file}.php' must return an array."
            );
        }

        self::$loaded[$file] = $config;

        return $config;
    }

    /**
     * Clear loaded configuration cache.
     */
    public static function clear(): void
    {
        self::$loaded = [];
    }
}
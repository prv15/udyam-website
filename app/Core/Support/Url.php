<?php

namespace App\Core\Support;

class Url
{
    /**
     * Base URL of the application.
     */
    public static function base(): string
    {
        return rtrim(APP_URL, '/');
    }

    /**
     * Generate URL.
     */
    public static function to(string $path = ''): string
    {
        return self::base() . '/' . ltrim($path, '/');
    }

    /**
     * Asset URL.
     */
    public static function asset(string $path): string
    {
        return self::to('assets/' . ltrim($path, '/'));
    }

    /**
     * Current URL.
     */
    public static function current(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            ? 'https'
            : 'http';

        return $scheme . '://' .
            $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI'];
    }
}
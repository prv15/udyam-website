<?php

declare(strict_types=1);

use App\Config\App;

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        $basePath = App::basePath();
        $path = '/' . ltrim($path, '/');

        return ($basePath === '' ? '' : $basePath) . $path;
    }
}

if (!function_exists('media_url')) {
    function media_url(array $media): string
    {
        return url('/uploads/media/' . trim((string) $media['folder'], '/') . '/' . rawurlencode((string) $media['filename']));
    }
}

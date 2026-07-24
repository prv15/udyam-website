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

if (!function_exists('upload_url')) {
    /**
     * Build a deployment-aware URL for a file inside /uploads.
     *
     * Content may have been created on localhost (/udyamventures), on a
     * staging subfolder (/demo), or at the domain root. Only the path from
     * /uploads onward is persisted conceptually; APP_BASE_PATH supplies the
     * current deployment prefix at runtime.
     */
    function upload_url(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (
            str_starts_with($path, 'data:')
            || str_starts_with($path, 'blob:')
            || str_starts_with($path, '//')
        ) {
            return $path;
        }

        $uploadPosition = strpos($path, '/uploads/');
        if ($uploadPosition !== false) {
            return url(substr($path, $uploadPosition));
        }

        if (str_starts_with(ltrim($path, '/'), 'uploads/')) {
            return url('/' . ltrim($path, '/'));
        }

        return $path;
    }
}

if (!function_exists('resolve_upload_urls')) {
    /**
     * Recursively normalise uploaded-file paths contained in section JSON.
     */
    function resolve_upload_urls(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = resolve_upload_urls($item);
            }
            return $value;
        }

        if (is_string($value) && (
            str_contains($value, '/uploads/')
            || str_starts_with(ltrim($value, '/'), 'uploads/')
        )) {
            return upload_url($value);
        }

        return $value;
    }
}
